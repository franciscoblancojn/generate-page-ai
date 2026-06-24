<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_API_SEO
{
    public static function init()
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes()
    {
        register_rest_route(GPAI_KEY, '/seo', [
            'methods' => 'POST',
            'callback' => [self::class, 'handleRequest'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);
    }

    public static function checkPermission($request)
    {
        $config = new GPAI_USE_DATA_CONFIG();
        $data = $config->get();
        $apiSeo = isset($data['api_seo']) ? $data['api_seo'] : [];

        if (empty($apiSeo['enabled']) || empty($apiSeo['key'])) {
            return new WP_Error('api_disabled', 'API SEO deshabilitada.', ['status' => 403]);
        }

        $headerKey = $request->get_header('X-GPAI-SEO-Key');
        if (!$headerKey) {
            return new WP_Error('missing_key', 'API Key requerida en header X-GPAI-SEO-Key.', ['status' => 401]);
        }

        if (!hash_equals($apiSeo['key'], $headerKey)) {
            return new WP_Error('invalid_key', 'API Key inválida.', ['status' => 401]);
        }

        return true;
    }

    public static function handleRequest($request)
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

        $allowed = array_keys(GPAI_SEO::getFields());
        $seoData = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $seoData[$key] = $value;
            }
        }

        if (empty($seoData)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'No se enviaron campos SEO válidos.',
            ], 400);
        }

        GPAI_SEO::SET($post_id, $seoData);

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'API_SEO_SAVE',
            'post_id' => $post_id,
            'fields' => array_keys($seoData),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Datos SEO guardados correctamente.',
            'data' => [
                'post_id' => $post_id,
                'saved' => $seoData,
            ],
        ], 200);
    }
}
