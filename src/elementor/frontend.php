<?php

function GPAI_replace_custom_vars_elementor_widget($content, $widget = null)
{
    return GPAI_replace_custom_vars($content);
}

add_filter('elementor/frontend/the_content', 'GPAI_replace_custom_vars');
add_filter('elementor/widget/render_content', 'GPAI_replace_custom_vars_elementor_widget', 10, 2);
