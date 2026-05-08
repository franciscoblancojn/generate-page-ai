<?php
function GPAI_replace_custom_vars($content)
{
    preg_match_all('/{{(.*?)}}|__(.*?)__/', $content, $matches);

    // unir ambos grupos y limpiar
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

    return $content;
}

add_filter('the_content', 'GPAI_replace_custom_vars');
// add_filter('elementor/widget/render_content', 'GPAI_replace_custom_vars');