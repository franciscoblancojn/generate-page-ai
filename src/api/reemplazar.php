<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_REEMPLAZAR
{
    public static function init()
    {
        add_action('wp_ajax_gpai_reemplazar_url', [self::class, 'reemplazarAjax']);
    }

    public static function reemplazarAjax()
    {
        check_ajax_referer('gpai_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'No tienes permisos para realizar esta acción.']);
        }

        $url_vieja = self::sanitizeUrlField($_POST['url_vieja'] ?? '');
        $url_nueva = self::sanitizeUrlField($_POST['url_nueva'] ?? '');
        $agregar_redirect = isset($_POST['agregar_redirect']) && $_POST['agregar_redirect'] === '1';

        if ($url_vieja === '' || $url_nueva === '') {
            wp_send_json_error(['message' => 'Debes ingresar la URL vieja y la URL nueva.']);
        }

        if ($url_vieja === $url_nueva) {
            wp_send_json_error(['message' => 'La URL vieja y la URL nueva deben ser diferentes.']);
        }

        $result = self::replaceUrl($url_vieja, $url_nueva);
        $rule = self::buildRedirectRule($url_vieja, $url_nueva);

        $redirect = [
            'status' => 'skipped',
            'message' => 'No se agregó redirección en .htaccess (checkbox desactivado).',
        ];

        if ($agregar_redirect) {
            $htaccess = new GPAI_USE_DATA_HTACCESS();
            $info = $htaccess->get();

            if (!$info['writable']) {
                $redirect = [
                    'status' => 'error',
                    'message' => 'El archivo .htaccess no tiene permisos de escritura.',
                ];
            } else {
                $res = self::addRedirectToHtaccess($url_vieja, $url_nueva);
                $redirect = [
                    'status' => $res['status'],
                    'message' => $res['message'],
                ];
            }
        }

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'reemplazar_url',
            'url_vieja' => $url_vieja,
            'url_nueva' => $url_nueva,
            'agregar_redirect' => $agregar_redirect ? '1' : '0',
            'report' => $result['report'] ?? [],
            'redirect' => $redirect,
        ]);

        if ($result['status'] !== 'ok') {
            wp_send_json_error([
                'message' => $result['message'],
                'report' => $result['report'] ?? [],
                'rule' => $rule,
                'redirect' => $redirect,
            ]);
        }

        wp_send_json_success([
            'message' => 'Reemplazo completado.',
            'report' => $result['report'],
            'rule' => $rule,
            'redirect' => $redirect,
        ]);
    }

    public static function sanitizeUrlField($value)
    {
        $value = wp_unslash($value);
        $value = wp_check_invalid_utf8((string) $value);
        $value = wp_strip_all_tags($value);
        $value = wp_kses_no_null($value);
        return trim($value);
    }

    public static function replaceUrl($search, $replace)
    {
        global $wpdb;

        $search = (string) $search;
        $replace = (string) $replace;

        if ($search === '' || $search === $replace) {
            return [
                'status' => 'error',
                'message' => 'Las URLs deben ser diferentes y no estar vacías.',
                'report' => [],
            ];
        }

        self::maximizeLimits();

        $tables = $wpdb->get_col('SHOW TABLES');
        $report = [
            'tables' => count($tables),
            'tables_with_changes' => 0,
            'rows_updated' => 0,
            'cells_updated' => 0,
            'tables_skipped' => [],
            'errors' => [],
            'elapsed' => 0,
        ];
        $start = microtime(true);

        foreach ($tables as $table) {
            self::processTable($table, $search, $replace, $report);
        }

        $report['elapsed'] = round(microtime(true) - $start, 2);

        return [
            'status' => 'ok',
            'message' => 'Reemplazo completado.',
            'report' => $report,
        ];
    }

    private static function maximizeLimits()
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }
        if (function_exists('ini_set')) {
            @ini_set('max_execution_time', 600);
            @ini_set('memory_limit', '512M');
        }
    }

    private static function processTable($table, $search, $replace, &$report)
    {
        global $wpdb;

        $skipColumns = array('guid');
        $changedInTable = false;

        $columns = $wpdb->get_results("SHOW FULL COLUMNS FROM `$table`");
        if (empty($columns)) {
            $report['tables_skipped'][] = $table;
            return;
        }

        $primaryKeys = $wpdb->get_col("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'", 4);

        $columnList = array();
        foreach ($columns as $column) {
            $columnList[] = $column->Field;
        }

        $chunk = 500;
        $offset = 0;

        while (true) {
            $rows = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `$table` LIMIT %d OFFSET %d", $chunk, $offset),
                ARRAY_A
            );

            if (empty($rows)) {
                break;
            }

            foreach ($rows as $row) {
                $update = array();
                $where = array();

                foreach ($columnList as $column) {
                    if (in_array($column, $skipColumns, true)) {
                        continue;
                    }
                    if (!array_key_exists($column, $row)) {
                        continue;
                    }

                    $value = $row[$column];
                    if ($value === null || $value === '') {
                        continue;
                    }

                    $newValue = self::recursiveUnserializeReplace($search, $replace, $value);
                    if ($newValue !== $value) {
                        $update[$column] = $newValue;
                    }
                }

                if (empty($update)) {
                    continue;
                }

                if (!empty($primaryKeys)) {
                    foreach ($primaryKeys as $pk) {
                        if (array_key_exists($pk, $row)) {
                            $where[$pk] = $row[$pk];
                        }
                    }
                } else {
                    $where = $row;
                }

                if (empty($where)) {
                    continue;
                }

                $updateClause = array();
                foreach ($update as $column => $value) {
                    $updateClause[] = "`" . $column . "` = " . self::sqlValue($value);
                }

                $whereClause = array();
                foreach ($where as $column => $value) {
                    $whereClause[] = "`" . $column . "` = " . self::sqlValue($value);
                }

                $sql = "UPDATE `$table` SET " . implode(', ', $updateClause) . ' WHERE ' . implode(' AND ', $whereClause);
                $result = $wpdb->query($sql);

                if ($result === false) {
                    $report['errors'][] = $wpdb->last_error;
                    continue;
                }

                $report['rows_updated']++;
                $report['cells_updated'] += count($update);
                $changedInTable = true;
            }

            $offset += $chunk;

            if (count($rows) < $chunk) {
                break;
            }
        }

        if ($changedInTable) {
            $report['tables_with_changes']++;
        }
    }

    private static function recursiveUnserializeReplace($from, $to, $data, $serialised = false)
    {
        try {
            if (is_string($data) && ($unserialized = @unserialize($data)) !== false) {
                $data = self::recursiveUnserializeReplace($from, $to, $unserialized, true);
            } elseif (is_array($data)) {
                $tmp = array();
                foreach ($data as $key => $value) {
                    $tmp[$key] = self::recursiveUnserializeReplace($from, $to, $value, false);
                }
                $data = $tmp;
                unset($tmp);
            } elseif (is_object($data)) {
                if (get_class($data) === '__PHP_Incomplete_Class') {
                    return $data;
                }
                $tmp = clone $data;
                $props = get_object_vars($data);
                foreach ($props as $key => $value) {
                    $tmp->$key = self::recursiveUnserializeReplace($from, $to, $value, false);
                }
                $data = $tmp;
                unset($tmp);
            } else {
                if (is_string($data)) {
                    $data = str_replace($from, $to, $data);
                }
            }

            if ($serialised) {
                return serialize($data);
            }
        } catch (Exception $error) {
            // Se conserva el valor original.
        }

        return $data;
    }

    private static function sqlValue($value)
    {
        global $wpdb;

        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return "'" . esc_sql($value) . "'";
    }

    public static function buildRedirectRule($urlVieja, $urlNueva)
    {
        $old = trim((string) $urlVieja, '/');
        $new = '/' . ltrim((string) $urlNueva, '/');

        if ($old === '' || $new === '/') {
            return '';
        }

        $old = str_replace(' ', '%20', $old);
        $new = str_replace(' ', '%20', $new);

        $pattern = '^' . preg_quote($old, '~') . '/?$';

        return 'RewriteRule ' . $pattern . ' ' . $new . ' [R=301,L]';
    }

    public static function addRedirectToHtaccess($urlVieja, $urlNueva)
    {
        $rule = self::buildRedirectRule($urlVieja, $urlNueva);
        if ($rule === '') {
            return [
                'status' => 'error',
                'message' => 'No se pudo construir la redirección.',
            ];
        }

        $htaccess = new GPAI_USE_DATA_HTACCESS();
        $info = $htaccess->get();

        if (!$info['writable']) {
            return [
                'status' => 'error',
                'message' => 'El archivo .htaccess no tiene permisos de escritura.',
            ];
        }

        $content = trim((string) $info['content']);

        if (self::ruleExists($content, $rule)) {
            return [
                'status' => 'ok',
                'message' => 'La redirección ya existía en .htaccess.',
            ];
        }

        if (!$info['exists']) {
            $htaccess->backup();
        }

        $hasBlock = strpos($content, '# GPAI Redirect URL') !== false;

        if ($hasBlock) {
            $count = 0;
            $newContent = preg_replace(
                '/(# GPAI Redirect URL[\s\S]*?)<\/IfModule>/',
                '$1' . $rule . "\n</IfModule>",
                $content,
                1,
                $count
            );

            if ($newContent !== null && $count > 0) {
                $content = $newContent;
            } else {
                $block = "# GPAI Redirect URL\n"
                    . "<IfModule mod_rewrite.c>\n"
                    . "RewriteEngine On\n"
                    . $rule . "\n"
                    . "</IfModule>\n";

                if (strpos($content, '# BEGIN WordPress') !== false) {
                    $content = str_replace('# BEGIN WordPress', $block . "\n# BEGIN WordPress", $content);
                } else {
                    $content = trim($content . "\n\n" . $block);
                }
            }
        } else {
            $block = "# GPAI Redirect URL\n"
                . "<IfModule mod_rewrite.c>\n"
                . "RewriteEngine On\n"
                . $rule . "\n"
                . "</IfModule>\n";

            if (strpos($content, '# BEGIN WordPress') !== false) {
                $content = str_replace('# BEGIN WordPress', $block . "\n# BEGIN WordPress", $content);
            } else {
                $content = trim($content . "\n\n" . $block);
            }
        }

        $saved = $htaccess->save($content);

        if ($saved) {
            return [
                'status' => 'ok',
                'message' => 'Redirección agregada a .htaccess.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'No se pudo guardar .htaccess.',
        ];
    }

    private static function ruleExists($content, $rule)
    {
        if (strpos($content, $rule) !== false) {
            return true;
        }

        $normalized = self::normalizeForCompare($content);
        $ruleNorm = self::normalizeForCompare($rule);

        return strpos($normalized, $ruleNorm) !== false;
    }

    private static function normalizeForCompare($text)
    {
        $text = str_replace('\\-', '-', $text);
        $text = str_replace('\\.', '.', $text);
        $text = str_replace('\\/', '/', $text);
        $text = str_replace('\\~', '~', $text);
        $text = str_replace('\\^', '^', $text);
        $text = str_replace('\\$', '$', $text);
        return $text;
    }
}

add_action('admin_init', ['GPAI_REEMPLAZAR', 'init']);