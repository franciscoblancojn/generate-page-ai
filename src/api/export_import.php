<?php

class GPAI_EXPORT_IMPORT
{
    public static function init()
    {
        add_action('wp_ajax_gpai_export_post', [self::class, 'exportPost']);
        add_action('wp_ajax_gpai_import_post', [self::class, 'importPost']);
        add_action('wp_ajax_gpai_export_template', [self::class, 'exportTemplate']);
        add_action('wp_ajax_gpai_import_template', [self::class, 'importTemplate']);
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
            'camposYoast'          => [],
            'camposGpaiSeo'        => [],
            'camposPlantillas'     => [],
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

        $yoastFields = GPAI_YOAST::GET($post_id);
        if (is_array($yoastFields)) {
            foreach ($yoastFields as $key => $value) {
                $data['camposYoast'][] = [
                    'key'      => $key,
                    'key_html' => '{{' . $key . '}}',
                    'valor'    => is_string($value) ? $value : '',
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

        $template_ids = GPAI_CF_TEMPLATE::getPostTemplates($post_id);
        foreach ($template_ids as $tpl_id) {
            $tpl = get_post($tpl_id);
            if (!$tpl) continue;

            $tplVars = GPAI_CF_TEMPLATE::GET($tpl_id);
            $campos = [];
            foreach ($tplVars as $key => $defaultVal) {
                $postVal  = get_post_meta($post_id, 'global_' . $key, true);
                $override = $postVal !== '';
                $campos[] = [
                    'key'           => $key,
                    'key_html'      => '{g{' . $key . '}}',
                    'valor'         => $override ? $postVal : $defaultVal,
                    'sobreescribir' => $override,
                ];
            }
            $data['camposPlantillas'][] = [
                'plantilla' => $tpl->post_title,
                'id'        => (string)$tpl_id,
                'campos'    => $campos,
            ];
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

        if (!empty($import['camposYoast'])) {
            $yf = [];
            foreach ($import['camposYoast'] as $campo) {
                $yf[sanitize_text_field($campo['key'])] = wp_kses_post($campo['valor']);
            }
            GPAI_YOAST::SET($post_id, $yf);
        }

        if (!empty($import['camposGpaiSeo'])) {
            $gf = [];
            foreach ($import['camposGpaiSeo'] as $campo) {
                $gf[sanitize_text_field($campo['key'])] = wp_kses_post($campo['valor']);
            }
            GPAI_SEO::SET($post_id, $gf);
        }

        if (!empty($import['camposPlantillas'])) {
            foreach ($import['camposPlantillas'] as $tplData) {
                $tpl_id = intval($tplData['id']);
                if (!get_post($tpl_id)) continue;

                $tpl_values = [];
                foreach ($tplData['campos'] as $campo) {
                    $tpl_values[sanitize_text_field($campo['key'])] = wp_kses_post($campo['valor']);
                }
                GPAI_CF_TEMPLATE::SET($tpl_id, $tpl_values);

                foreach ($tplData['campos'] as $campo) {
                    $key = sanitize_text_field($campo['key']);
                    if (!empty($campo['sobreescribir'])) {
                        update_post_meta($post_id, 'global_' . $key, wp_kses_post($campo['valor']));
                    } else {
                        delete_post_meta($post_id, 'global_' . $key);
                    }
                }
            }
        }

        wp_send_json_success(['message' => 'Importación completada. Recarga la página para ver los cambios.']);
    }

    public static function exportTemplate()
    {
        if (!current_user_can('manage_options')) wp_die(-1);

        $template_id = intval($_POST['template_id'] ?? 0);
        $tpl = get_post($template_id);
        if (!$tpl) wp_send_json_error(['message' => 'Plantilla no existe.']);

        $tplVars = GPAI_CF_TEMPLATE::GET($template_id);
        $campos = [];
        foreach ($tplVars as $key => $value) {
            $campos[] = [
                'key'      => $key,
                'key_html' => '{g{' . $key . '}}',
                'valor'    => $value,
            ];
        }

        wp_send_json([
            'plantilla_id'        => $template_id,
            'plantilla_name'      => $tpl->post_title,
            'camposPersonalizados' => $campos,
        ]);
    }

    public static function importTemplate()
    {
        if (!current_user_can('manage_options')) wp_die(-1);

        $template_id = intval($_POST['template_id'] ?? 0);
        if (!get_post($template_id)) wp_send_json_error(['message' => 'Plantilla no existe.']);

        $raw = wp_unslash($_POST['data'] ?? '');
        $import = json_decode($raw, true);
        if (!$import) wp_send_json_error(['message' => 'JSON inválido.']);

        if (!empty($import['camposPersonalizados'])) {
            $values = [];
            foreach ($import['camposPersonalizados'] as $campo) {
                $values[sanitize_text_field($campo['key'])] = wp_kses_post($campo['valor']);
            }
            GPAI_CF_TEMPLATE::SET($template_id, $values);
        }

        wp_send_json_success(['message' => 'Importación completada. Recarga la página para ver los cambios.']);
    }
}

add_action('admin_init', ['GPAI_EXPORT_IMPORT', 'init']);
