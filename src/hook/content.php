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

add_filter('the_content', 'GPAI_replace_custom_vars');