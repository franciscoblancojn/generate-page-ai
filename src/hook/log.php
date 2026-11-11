<?php

/**
 * Limita el tamaño del log de FWUSystemLog (opción GPAI_LOG_KEY).
 *
 * FWUSystemLog guarda todo en una sola fila de wp_options y cada entrada incluye
 * $_POST completos, prompts y respuestas de la IA; con GPAI_LOG_COUNT por tipo la
 * fila llegó a decenas de MB. Como libs/ e index.php no se editan, se recorta aquí,
 * justo antes de que update_option() escriba.
 */
class GPAI_LOG_LIMIT
{
    const MAX_PER_TYPE = 20;
    const MAX_STRING = 1000;
    const MAX_ITEMS = 50;
    const MAX_DEPTH = 6;
    const MAX_BYTES = 524288; // 512 KB para toda la fila.
    const REDACT = '/api_?key|token|secret|password|pass$|authorization/i';

    public static function init()
    {
        add_filter('pre_update_option_' . GPAI_LOG_KEY, [self::class, 'limit'], 10, 1);
    }

    public static function limit($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $log = json_decode($value, true);
        if (!is_array($log)) {
            return '[]';
        }

        foreach ($log as $type => $entries) {
            if (!is_array($entries)) {
                unset($log[$type]);
                continue;
            }
            $entries = array_slice($entries, -self::MAX_PER_TYPE);
            foreach ($entries as $i => $entry) {
                $entries[$i] = self::shrink($entry, 0);
            }
            $log[$type] = $entries;
        }

        $json = wp_json_encode($log);

        // Si aún es muy grande, quita la entrada más vieja del tipo más largo.
        while ($json !== false && strlen($json) > self::MAX_BYTES) {
            $biggest = null;
            foreach ($log as $type => $entries) {
                if ($biggest === null || count($entries) > count($log[$biggest])) {
                    $biggest = $type;
                }
            }
            if ($biggest === null || count($log[$biggest]) === 0) {
                return '[]';
            }
            array_shift($log[$biggest]);
            if (count($log[$biggest]) === 0) {
                unset($log[$biggest]);
            }
            $json = wp_json_encode($log);
        }

        return $json === false ? '[]' : $json;
    }

    private static function shrink($value, $depth)
    {
        if (is_string($value)) {
            $len = strlen($value);
            if ($len > self::MAX_STRING) {
                return substr($value, 0, self::MAX_STRING) . '… [' . $len . ' bytes]';
            }
            return $value;
        }

        if (!is_array($value)) {
            return $value;
        }

        if ($depth >= self::MAX_DEPTH) {
            return '[' . count($value) . ' items]';
        }

        $out = [];
        $n = 0;
        foreach ($value as $k => $v) {
            if ($n++ >= self::MAX_ITEMS) {
                $out['…'] = (count($value) - self::MAX_ITEMS) . ' items más';
                break;
            }
            if (is_string($k) && preg_match(self::REDACT, $k)) {
                $out[$k] = '[oculto]';
                continue;
            }
            $out[$k] = self::shrink($v, $depth + 1);
        }
        return $out;
    }
}

GPAI_LOG_LIMIT::init();
