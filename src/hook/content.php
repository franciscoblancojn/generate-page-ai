<?php
function GPAI_replace_custom_vars($content)
{
    preg_match_all('/{{(.*?)}}|__(.*?)__/', $content, $matches);

    $keys = array_filter(array_merge($matches[1], $matches[2]));

    foreach ($keys as $key) {
        $value = null;

        if (current_user_can('manage_options') && isset($_GET[$key]) && $_GET[$key] !== '') {
            $value = sanitize_text_field($_GET[$key]);
        } else {
            $value = get_post_meta(get_the_ID(), $key, true);
        }

        if (!empty($value)) {
            $content = str_replace(
                ["{{{$key}}}", "__{$key}__"],
                $value,
                $content
            );
        }
    }

    preg_match_all('/\{g\{(.*?)\}\}/', $content, $gmatches);

    foreach ($gmatches[1] as $key) {
        $gkey = '_g_' . $key;
        $value = null;

        if (current_user_can('manage_options') && isset($_GET[$gkey]) && $_GET[$gkey] !== '') {
            $value = sanitize_text_field($_GET[$gkey]);
        } else {
            $value = get_post_meta(get_the_ID(), $gkey, true);
        }

        if (!empty($value)) {
            $content = str_replace("{g{{$key}}}", $value, $content);
        }
    }

    return $content;
}

add_filter('the_content', 'GPAI_replace_custom_vars');