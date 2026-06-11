<?php

class GPAI_EXPORT_IMPORT
{
    public static function init()
    {
        add_action('wp_ajax_gpai_export_post', [self::class, 'exportPost']);
        add_action('wp_ajax_gpai_import_post', [self::class, 'importPost']);
    }

    public static function exportPost()
    {
        if (!current_user_can('manage_options')) wp_die(-1);

        $post_id = intval($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        if (!$post) wp_send_json_error(['message' => 'Post no existe.']);

        $data = [
            'post_id'     => $post_id,
            'post_name'   => $post->post_title,
            'camposPersonalisados' => [],
            'camposGpaiSeo'        => [],
        ];

        $customFields = GPAI_CF::GET($post_id);
        if (is_array($customFields) && !isset($customFields['success'])) {
            foreach ($customFields as $key => $value) {
                $data['camposPersonalisados'][] = [
                    'key'      => $key,
                    'key_html' => '{{' . $key . '}}',
                    'valor'    => $value,
                ];
            }
        }

        $gpaiSeoFields = GPAI_SEO::GET($post_id);
        if (is_array($gpaiSeoFields)) {
            foreach ($gpaiSeoFields as $key => $value) {
                $data['camposGpaiSeo'][] = [
                    'key'      => $key,
                    'key_html' => '{{' . $key . '}}',
                    'valor'    => is_string($value) ? $value : '',
                ];
            }
        }

        wp_send_json($data);
    }

    public static function importPost()
    {
        if (!current_user_can('manage_options')) wp_die(-1);

        $post_id = intval($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        if (!$post) wp_send_json_error(['message' => 'Post no existe.']);

        $raw = wp_unslash($_POST['data'] ?? '');
        $import = json_decode($raw, true);
        if (!$import) wp_send_json_error(['message' => 'JSON inválido.']);

        if (!empty($import['camposPersonalisados'])) {
            $cf = [];
            foreach ($import['camposPersonalisados'] as $campo) {
                $cf[sanitize_text_field($campo['key'])] = wp_kses_post($campo['valor']);
            }
            GPAI_CF::SET($post_id, $cf);
        }

        if (!empty($import['camposGpaiSeo'])) {
            $gf = [];
            foreach ($import['camposGpaiSeo'] as $campo) {
                $gf[sanitize_text_field($campo['key'])] = wp_kses_post($campo['valor']);
            }
            GPAI_SEO::SET($post_id, $gf);
        }

        wp_send_json_success(['message' => 'Importación completada. Recarga la página para ver los cambios.']);
    }
}

add_action('admin_init', ['GPAI_EXPORT_IMPORT', 'init']);
