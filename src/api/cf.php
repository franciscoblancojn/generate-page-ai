<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_CF
{
    public static function init()
    {
        register_rest_route(GPAI_KEY, '/cf/get', [
            'methods' => 'GET',
            'callback' => [self::class, 'GET_Enpoint'],
        ]);
        register_rest_route(GPAI_KEY, '/cf/set', [
            'methods' => 'POST',
            'callback' => [self::class, 'SET_Enpoint'],
        ]);
    }
    private static function extractKeys($data, &$keys)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {

                // 🔥 también revisar claves (por si acaso)
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

            if (preg_match_all('/{{(.*?)}}|__(.*?)__/', $data, $matches)) {

                $foundKeys = array_merge(
                    array_filter($matches[1]),
                    array_filter($matches[2])
                );

                foreach ($foundKeys as $key) {
                    $keys[] = trim($key);
                }
            }
        }
    }
    public static function GET($post_id)
    {
        if (!get_post($post_id)) {
            return [
                'success' => false,
                'message' => 'Post no existe'
            ];
        }

        $result = [];
        $keys = [];

        // 🔹 1. Contenido normal (post_content)
        $content = get_post_field('post_content', $post_id);

        if ($content) {
            self::extractKeys($content, $keys);
        }

        // 🔹 2. Elementor (_elementor_data)
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);

        if ($elementor_data) {
            $data = json_decode($elementor_data, true);

            if (is_array($data)) {
                self::extractKeys($data, $keys);
            }
        }

        // 🔥 3. Eliminar duplicados
        $keys = array_unique($keys);

        // 🔹 4. Obtener valores
        foreach ($keys as $key) {
            $value = get_post_meta($post_id, $key, true);
            $result[$key] = $value;
        }

        return $result;
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
        return self::GET($post_id);
    }
    public static function SET($post_id, $data)
    {
        $result = [];
        // Validar post_id
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
        // Recorrer campos
        foreach ($data as $key => $value) {

            $sanitized = is_array($value)
                ? array_map('wp_kses_post', $value)
                : wp_kses_post($value);

            update_post_meta($post_id, $key, $sanitized);

            $result[$key] = $sanitized;
        }


        FWUSystemLog::add(GPAI_KEY, [
            'type' => "GPAI_CF SET",
            'data' => $data,
            'result' => $result,
        ]);
        return $result;
    }
    public static function SET_Enpoint($request)
    {
        $data = $request->get_json_params();
        return self::SET($data['post_id'], $data);
    }
    public static function save_from_elementor_ajax()
    {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $key = isset($_POST['key']) ? sanitize_key($_POST['key']) : '';
        $value = isset($_POST['value']) ? wp_kses_post($_POST['value']) : '';
        $prefix = isset($_POST['prefix']) ? sanitize_key($_POST['prefix']) : '';

        if (!$post_id || !$key) {
            wp_send_json_error('Datos inválidos. post_id y key son requeridos.');
        }

        if (!get_post($post_id)) {
            wp_send_json_error('El post no existe.');
        }

        $meta_key = $prefix ? $prefix . $key : $key;
        update_post_meta($post_id, $meta_key, $value);

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_CF_ELEMENTOR',
            'post_id' => $post_id,
            'key' => $meta_key,
            'value' => $value,
        ]);

        wp_send_json_success([
            'key' => $meta_key,
            'value' => $value,
            'message' => 'Campo personalizado guardado correctamente.'
        ]);
    }

    public static function delete_custom_field_ajax()
    {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $key = isset($_POST['key']) ? sanitize_key($_POST['key']) : '';
        $prefix = isset($_POST['prefix']) ? sanitize_key($_POST['prefix']) : '';

        if (!$post_id || !$key) {
            wp_send_json_error('post_id y key son requeridos.');
        }

        if (!get_post($post_id)) {
            wp_send_json_error('El post no existe.');
        }

        $meta_key = $prefix ? $prefix . $key : $key;
        delete_post_meta($post_id, $meta_key);

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_CF_DELETE',
            'post_id' => $post_id,
            'key' => $key,
        ]);

        wp_send_json_success([
            'message' => 'Campo personalizado eliminado correctamente.'
        ]);
    }

    public static function list_custom_fields_ajax()
    {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error('post_id es requerido.');
        }

        if (!get_post($post_id)) {
            wp_send_json_error('El post no existe.');
        }

        $meta = get_post_meta($post_id);
        $result = [];
        $seo_used = [];

        foreach ($meta as $key => $values) {
            if (strpos($key, '_') !== 0) {
                $result[$key] = [
                    'key' => $key,
                    'value' => $values[0],
                    'type' => 'custom',
                ];
                if (strpos($key, 'gpai_wpseo_') === 0) {
                    $seo_used[$key] = true;
                }
            }
        }

        $seo_fields = GPAI_SEO::getFields();
        foreach ($seo_fields as $key => $label) {
            if (!isset($seo_used[$key])) {
                $result[$key] = [
                    'key' => $key,
                    'value' => '',
                    'type' => 'custom',
                ];
            }
        }

        wp_send_json_success(array_values($result));
    }
}

add_action('wp_ajax_gpai_save_custom_field', ['GPAI_CF', 'save_from_elementor_ajax']);
add_action('wp_ajax_gpai_list_custom_fields', ['GPAI_CF', 'list_custom_fields_ajax']);
add_action('wp_ajax_gpai_delete_custom_field', ['GPAI_CF', 'delete_custom_field_ajax']);
