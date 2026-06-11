<?php

function GPAI_get_global_field($key)
{
    $value = get_option('GPAI_GLOBAL_FIELDS_' . $key, '');
    if ($value !== '') {
        return $value;
    }
    return null;
}

function GPAI_replace_custom_vars($content, $depth = 0)
{
    if (isset($_GET['GPAI_CUSTOM_FIELDS_DISABLE'])) {
        return $content;
    }

    // Evita loops infinitos
    if ($depth > 10) {
        return $content;
    }

    $original_content = $content;

    // Cargar datos de preview por UUID (evita URL demasiado larga)
    $previewMap = [];
    if (isset($_GET['GPAI_preview_variation'])) {
        $uuid = sanitize_key($_GET['GPAI_preview_variation']);
        $data = get_transient('gpai_pv_' . $uuid);
        if (is_array($data)) {
            foreach (['customFields', 'gpaiSeoFields', 'globalFields'] as $group) {
                if (isset($data[$group]) && is_array($data[$group])) {
                    foreach ($data[$group] as $k => $v) {
                        $previewMap[$k] = $v;
                    }
                }
            }
        } else {
            return "URL EXPIRED";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reemplazo {{x}} y __x__
    |--------------------------------------------------------------------------
    */
    preg_match_all('/{{(.*?)}}|__(.*?)__/', $content, $matches);

    $keys = array_filter(array_merge($matches[1], $matches[2]));

    foreach ($keys as $key) {

        $value = null;

        if (array_key_exists($key, $previewMap)) {
            $value = $previewMap[$key];
        } elseif (
            current_user_can('manage_options') &&
            isset($_GET[$key]) &&
            $_GET[$key] !== ''
        ) {
            $value = sanitize_text_field($_GET[$key]);
        } else {
            $value = get_post_meta(get_the_ID(), $key, true);
        }

        if (($value === null || $value === '') && function_exists('GPAI_get_global_field')) {
            $global_value = GPAI_get_global_field($key);
            if ($global_value !== null && $global_value !== '') {
                $value = $global_value;
            }
        }

        if ($value !== null && $value !== '') {

            $content = str_replace(
                ["{{{$key}}}", "__{$key}__"],
                $value,
                $content
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reemplazo recursivo
    |--------------------------------------------------------------------------
    */
    if ($content !== $original_content) {

        return GPAI_replace_custom_vars($content, $depth + 1);
    }

    return $content;
}

add_filter('the_content', 'GPAI_replace_custom_vars', 20);

add_action('template_redirect', function () {

    if (isset($_GET['GPAI_DISABLE'])) {
        return;
    }
    if (isset($_GET['GPAI_EDIT'])) {
        return;
    }
    if (isset($_GET['preview']) && $_GET['preview'] == 'true') {
        return;
    }
    if (isset($_GET["action"]) && $_GET["action"] == "elementor") {
        return;
    }
    if (
        defined('ELEMENTOR_VERSION') &&
        (
            \Elementor\Plugin::$instance->editor->is_edit_mode()
            || \Elementor\Plugin::$instance->preview->is_preview_mode()
        )
    ) {
        return;
    }
    // Admin
    if (is_admin()) {
        return;
    }

    // AJAX
    if (wp_doing_ajax()) {
        return;
    }

    // REST API
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }
    if (!is_page()) {
        return;
    }

    global $post;

    if (!$post) {
        return;
    }
    $post_id = $post->ID;

    $content_independiente = get_post_meta($post_id, GPAI_CONTENT_INDEPENDIENTE_META, true);
    if ($content_independiente !== '0') {
        return;
    }

    $parent_id = get_post_meta($post_id, GPAI_KEY . '_PARENT', true);
    if (!$parent_id) {
        return;
    }

    $parent_post = get_post($parent_id);
    if (!$parent_post) {
        return;
    }

    $url = get_permalink($parent_id) . "?GPAI_CUSTOM_FIELDS_DISABLE&STPA_DISABLE&GPAI_DISABLE";
    $html = @file_get_contents($url);
    if ($html === false) {
        return;
    }

    $html = GPAI_replace_custom_vars($html);

    $html = preg_replace(
        '/\s*<!-- GPAI SEO Meta Tags -->.*?<!-- \/GPAI SEO Meta Tags -->\s*/s',
        "\n",
        $html
    );

    $html = preg_replace(
        '/<script\b[^>]*class="yoast-schema-graph"[^>]*>.*?<\/script>\s*\n?/is',
        '',
        $html
    );

    $child_title = wp_get_document_title();
    $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . esc_html($child_title) . '</title>', $html, 1);

    ob_start();
    GPAI_SEO_output();
    $child_seo = ob_get_clean();
    if ($child_seo) {
        $html = str_replace('</head>', $child_seo . "\n</head>", $html);
    }

    echo $html;
    exit;
}, 2);
