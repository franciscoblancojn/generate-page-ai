<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_CF_TEMPLATE
{
    public static function getTemplates()
    {
        $templates = get_posts([
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        $result = [];
        foreach ($templates as $t) {
            $result[$t->ID] = $t->post_title;
        }
        return $result;
    }

    private static function extractKeys($data, &$keys)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($key)) {
                    self::extractKeys($key, $keys);
                }
                self::extractKeys($value, $keys);
            }
        } elseif (is_object($data)) {
            foreach ((array)$data as $value) {
                self::extractKeys($value, $keys);
            }
        } elseif (is_string($data)) {
            if (preg_match_all('/\{g\{(.*?)\}\}/', $data, $matches)) {
                foreach ($matches[1] as $key) {
                    $keys[] = trim($key);
                }
            }
        }
    }

    public static function getPostTemplates($post_id)
    {
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        if (!$elementor_data) return [];

        $data = json_decode($elementor_data, true);
        if (!is_array($data)) return [];

        $template_ids = [];
        self::extractTemplateIds($data, $template_ids);

        $result = [];
        foreach (array_unique($template_ids) as $id) {
            if (get_post_type($id) === 'elementor_library') {
                $result[] = (int)$id;
            }
        }

        return $result;
    }

    public static function getPostTemplate($post_id)
    {
        $templates = self::getPostTemplates($post_id);
        return !empty($templates) ? $templates[0] : null;
    }

    private static function extractTemplateIds($data, &$ids)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'template_id' && (is_numeric($value) || is_string($value))) {
                    $id = (int)$value;
                    if ($id > 0) $ids[] = $id;
                }
                self::extractTemplateIds($value, $ids);
            }
        } elseif (is_object($data)) {
            foreach ((array)$data as $value) {
                self::extractTemplateIds($value, $ids);
            }
        }
    }

    public static function GET($template_id)
    {
        if (!get_post($template_id)) {
            return [
                'success' => false,
                'message' => 'Template no existe'
            ];
        }

        $result = [];
        $keys = [];

        $elementor_data = get_post_meta($template_id, '_elementor_data', true);

        if ($elementor_data) {
            $data = json_decode($elementor_data, true);
            if (is_array($data)) {
                self::extractKeys($data, $keys);
            }
        }

        $keys = array_unique($keys);

        foreach ($keys as $key) {
            $value = get_post_meta($template_id, '_g_' . $key, true);
            $result[$key] = $value;
        }

        return $result;
    }

    public static function SET($template_id, $data)
    {
        $result = [];

        if (empty($template_id)) {
            return [
                'success' => false,
                'message' => 'template_id es requerido'
            ];
        }

        $template_id = intval($template_id);
        if (!get_post($template_id)) {
            return [
                'success' => false,
                'message' => 'Template no existe'
            ];
        }

        foreach ($data as $key => $value) {
            $sanitized = is_array($value)
                ? array_map('sanitize_text_field', $value)
                : sanitize_text_field($value);

            update_post_meta($template_id, '_g_' . $key, $sanitized);
            $result[$key] = $sanitized;
        }

        FWUSystemLog::add(GPAI_KEY, [
            'type' => "GPAI_CF_TEMPLATE SET",
            'data' => $data,
            'result' => $result,
        ]);

        return $result;
    }
}
