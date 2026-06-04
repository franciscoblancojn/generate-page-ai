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
    if(isset($_GET['GPAI_CUSTOM_FIELDS_DISABLE'])){
        return $content;
    }

    // Evita loops infinitos
    if ($depth > 10) {
        return $content;
    }

    $original_content = $content;

    /*
    |--------------------------------------------------------------------------
    | Reemplazo {{x}} y __x__
    |--------------------------------------------------------------------------
    */
    preg_match_all('/{{(.*?)}}|__(.*?)__/', $content, $matches);

    $keys = array_filter(array_merge($matches[1], $matches[2]));

    foreach ($keys as $key) {

        $value = null;

        if (
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
    | Reemplazo {g{x}}
    |--------------------------------------------------------------------------
    */
    preg_match_all('/\{g\{(.*?)\}\}/', $content, $gmatches);

    foreach ($gmatches[1] as $key) {

        $value = null;
        $global_key = 'global_' . $key;

        if (
            current_user_can('manage_options') &&
            isset($_GET[$global_key]) &&
            $_GET[$global_key] !== ''
        ) {
            $value = sanitize_text_field($_GET[$global_key]);
        } else {
            $value = get_post_meta(get_the_ID(), $global_key, true);
        }

        if (empty($value)) {

            $template_ids = GPAI_CF_TEMPLATE::getPostTemplates(get_the_ID());

            foreach ($template_ids as $template_id) {

                $value = get_post_meta($template_id, '_g_' . $key, true);

                if (!empty($value)) {
                    break;
                }
            }
        }

        if ($value !== null && $value !== '') {

            $content = str_replace(
                "{g{{$key}}}",
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

function GPAI_inherit_parent_content($content)
{
    if (!is_singular() || is_admin()) {
        return $content;
    }

    $post_id = get_the_ID();
    if (!$post_id) {
        return $content;
    }

    $content_independiente = get_post_meta($post_id, GPAI_CONTENT_INDEPENDIENTE_META, true);
    if ($content_independiente != '0') {
        return $content;
    }

    $parent_id = get_post_meta($post_id, GPAI_KEY . '_PARENT', true);
    if (!$parent_id) {
        return $content;
    }

    $parent_post = get_post($parent_id);
    if (!$parent_post) {
        return $content;
    }
    
    return $parent_post->post_content;
}
add_filter('the_content', 'GPAI_inherit_parent_content', 10);

add_filter('the_content', 'GPAI_replace_custom_vars',20);

/*
|--------------------------------------------------------------------------
| INTERCEPTAR _elementor_data PARA PÁGINAS EN MODO HEREDADO
|--------------------------------------------------------------------------
*/

add_filter('get_post_metadata', function ($value, $object_id, $meta_key, $single) {
    if ($meta_key !== '_elementor_data') {
        return $value;
    }

    static $recursing = false;
    if ($recursing) {
        return $value;
    }
    $recursing = true;

    $content_independiente = get_post_meta($object_id, GPAI_CONTENT_INDEPENDIENTE_META, true);
    if ($content_independiente === '0') {
        $parent_id = get_post_meta($object_id, GPAI_KEY . '_PARENT', true);
        if ($parent_id) {
            $parent_value = get_post_meta($parent_id, $meta_key, true);
            if (!empty($parent_value)) {
                $recursing = false;
                return $single ? $parent_value : [$parent_value];
            }
        }
    }

    $recursing = false;
    return $value;
}, 50, 4);