<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_API_GF
{
    public static function init()
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes()
    {
        register_rest_route(GPAI_KEY, '/gf/get', [
            'methods' => 'GET',
            'callback' => [self::class, 'handleGet'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);

        register_rest_route(GPAI_KEY, '/gf/set', [
            'methods' => 'POST',
            'callback' => [self::class, 'handleSet'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);
    }

    private static function getConfig()
    {
        $config = new GPAI_USE_DATA_CONFIG();
        $data = $config->get();
        return isset($data['api_gf']) ? $data['api_gf'] : [];
    }

    public static function checkPermission($request)
    {
        $apiGf = self::getConfig();

        if (empty($apiGf['enabled']) || empty($apiGf['key'])) {
            return new WP_Error('api_disabled', 'API Global Fields deshabilitada.', ['status' => 403]);
        }

        $headerKey = $request->get_header('X-GPAI-GF-Key');
        if (!$headerKey) {
            return new WP_Error('missing_key', 'API Key requerida en header X-GPAI-GF-Key.', ['status' => 401]);
        }

        if (!hash_equals($apiGf['key'], $headerKey)) {
            return new WP_Error('invalid_key', 'API Key inválida.', ['status' => 401]);
        }

        return true;
    }

    public static function handleGet($request)
    {
        $gf = new GPAI_USE_DATA_GLOBAL_FIELDS();
        $fields = $gf->getAll();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'fields' => $fields,
            ],
        ], 200);
    }

    public static function handleSet($request)
    {
        $params = $request->get_json_params();

        if (empty($params) || !is_array($params)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'El body debe ser un objeto clave: valor.',
            ], 400);
        }

        $gf = new GPAI_USE_DATA_GLOBAL_FIELDS();
        $saved = [];

        foreach ($params as $key => $value) {
            $safeKey = sanitize_key($key);
            if ($safeKey === '') continue;
            $sanitized = wp_kses_post($value);
            $gf->setField($safeKey, $sanitized);
            $saved[$safeKey] = $sanitized;
        }

        if (empty($saved)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'No se enviaron campos globales válidos.',
            ], 400);
        }

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'API_GF_SET',
            'fields' => array_keys($saved),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Campos globales guardados correctamente.',
            'data' => [
                'saved' => $saved,
            ],
        ], 200);
    }
}
