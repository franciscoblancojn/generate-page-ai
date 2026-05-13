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
        $value = null;
        $global_key = 'global_' . $key;

        if (current_user_can('manage_options') && isset($_GET[$global_key]) && $_GET[$global_key] !== '') {
            $value = sanitize_text_field($_GET[$global_key]);
        } else {
            $value = get_post_meta(get_the_ID(), $global_key, true);
        }

        if (empty($value)) {
            $template_id = GPAI_CF_TEMPLATE::getPostTemplate(get_the_ID());
            if ($template_id) {
                $value = get_post_meta($template_id, '_g_' . $key, true);
            }
        }

        if (!empty($value)) {
            $content = str_replace("{g{{$key}}}", $value, $content);
        }
    }

    return $content;
}

add_filter('the_content', 'GPAI_replace_custom_vars');