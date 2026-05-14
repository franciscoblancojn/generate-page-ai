<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_YOAST
{
    public static function init()
    {
        register_rest_route(GPAI_KEY, '/yoast/get', [
            'methods' => 'GET',
            'callback' => [self::class, 'GET_Enpoint'],
        ]);

        register_rest_route(GPAI_KEY, '/yoast/set', [
            'methods' => 'POST',
            'callback' => [self::class, 'SET_Enpoint'],
        ]);
    }

    // ---------------- GET ----------------
    public static function GET($post_id)
    {
        global $wpdb;

        $excluded = [
            '_yoast_wpseo_content_score',
            '_yoast_wpseo_estimated-reading-time-minutes',
        ];

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "
            SELECT meta_key, meta_value
            FROM {$wpdb->postmeta}
            WHERE post_id = %d
            AND meta_key LIKE %s
            ",
                $post_id,
                '_yoast_wpseo_%'
            ),
            ARRAY_A
        );

        $yoast = [];

        foreach ($results as $row) {

            if (in_array($row['meta_key'], $excluded)) {
                continue;
            }

            $yoast[$row['meta_key']] =
                maybe_unserialize($row['meta_value']);
        }

        return  $yoast;
    }

    public static function GET_Enpoint($request)
    {
        $post_id = $request->get_param('post_id');

        if (!$post_id) {
            return [
                'success' => false,
                'message' => 'post_id es requerido'
            ];
        }

        $post_id = intval($post_id);

        if (!get_post($post_id)) {
            return [
                'success' => false,
                'message' => 'Post no existe'
            ];
        }

        return self::GET($post_id);
    }

    // ---------------- SET ----------------
    public static function SET($post_id, $data)
    {
        $result = [];

        // Validaciones
        if (empty($post_id)) {
            return [
                'success' => false,
                'message' => 'post_id es requerido'
            ];
        }

        $post_id = intval($post_id);

        if (!get_post($post_id)) {
            return [
                'success' => false,
                'message' => 'Post no existe'
            ];
        }

        // 🔥 SOLO guardar claves de Yoast
        foreach ($data as $key => $value) {

            if (strpos($key, '_yoast_wpseo_') !== 0) {
                continue;
            }

            if (is_array($value)) {
                $value = wp_json_encode($value);
            } else {
                $value = wp_kses_post($value);
            }

            update_post_meta($post_id, $key, $value);

            $result[$key] = $value;
        }

        // if (function_exists('wpseo_init')) {
        //     do_action('save_post', $post_id);
        // }

        FWUSystemLog::add(GPAI_KEY, [
            'type' => "GPAI_YOAST SET",
            'post_id' => $post_id,
            'data' => $data,
            'result' => $result,
        ]);

        return [
            'success' => true,
            'message' => 'Yoast actualizado correctamente',
            'data' => $result
        ];
    }

    public static function SET_Enpoint($request)
    {
        $data = $request->get_json_params();

        if (empty($data['post_id'])) {
            return [
                'success' => false,
                'message' => 'post_id es requerido'
            ];
        }

        return self::SET($data['post_id'], $data);
    }
}

// add_action('rest_api_init', ['GPAI_YOAST', 'init']);