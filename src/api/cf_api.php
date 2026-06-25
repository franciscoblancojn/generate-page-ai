<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_API_CF
{
    public static function init()
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes()
    {
        register_rest_route(GPAI_KEY, '/cf/get', [
            'methods' => 'GET',
            'callback' => [self::class, 'handleGet'],
            'permission_callback' => [self::class, 'checkPermission'],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route(GPAI_KEY, '/cf/set', [
            'methods' => 'POST',
            'callback' => [self::class, 'handleSet'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);
    }

    private static function getConfig()
    {
        $config = new GPAI_USE_DATA_CONFIG();
        $data = $config->get();
        return isset($data['api_cf']) ? $data['api_cf'] : [];
    }

    public static function checkPermission($request)
    {
        $apiCf = self::getConfig();

        if (empty($apiCf['enabled']) || empty($apiCf['key'])) {
            return new WP_Error('api_disabled', 'API Custom Fields deshabilitada.', ['status' => 403]);
        }

        $headerKey = $request->get_header('X-GPAI-CF-Key');
        if (!$headerKey) {
            return new WP_Error('missing_key', 'API Key requerida en header X-GPAI-CF-Key.', ['status' => 401]);
        }

        if (!hash_equals($apiCf['key'], $headerKey)) {
            return new WP_Error('invalid_key', 'API Key inválida.', ['status' => 401]);
        }

        return true;
    }

    public static function handleGet($request)
    {
        $post_id = $request->get_param('post_id');
        if (!$post_id) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'post_id es requerido.',
            ], 400);
        }

        $post_id = intval($post_id);
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Post no encontrado.',
            ], 404);
        }

        $customFields = GPAI_CF::GET($post_id);

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'fields' => $customFields,
            ],
        ], 200);
    }

    public static function handleSet($request)
    {
        $params = $request->get_json_params();

        if (empty($params['post_id'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'post_id es requerido.',
            ], 400);
        }

        $post_id = intval($params['post_id']);
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Post no encontrado.',
            ], 404);
        }

        // Extraer solo los campos personalizados (todo excepto post_id)
        $cfData = [];
        foreach ($params as $key => $value) {
            if ($key !== 'post_id') {
                $cfData[$key] = $value;
            }
        }

        if (empty($cfData)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'No se enviaron campos personalizados.',
            ], 400);
        }

        $result = GPAI_CF::SET($post_id, $cfData);

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'API_CF_SET',
            'post_id' => $post_id,
            'fields' => array_keys($cfData),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Campos personalizados guardados correctamente.',
            'data' => [
                'post_id' => $post_id,
                'saved' => $result,
            ],
        ], 200);
    }
}
