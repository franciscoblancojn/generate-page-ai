<?php

function GPAI_Elementor_Editor_Assets()
{
    if (!defined('ELEMENTOR_VERSION')) return;

    wp_enqueue_style(
        'gpai-elementor-editor',
        GPAI_URL . 'src/css/elementor-editor.css',
        [],
        '1.0.0.1'
    );

    wp_enqueue_script(
        'gpai-elementor-editor',
        GPAI_URL . 'src/js/elementor-editor.js',
        ['jquery'],
        '1.0.0.7',
        true
    );

    wp_localize_script('gpai-elementor-editor', 'gpaiEditor', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gpai_elementor_nonce'),
    ]);
}

add_action('elementor/editor/after_enqueue_scripts', 'GPAI_Elementor_Editor_Assets');
add_action('elementor/editor/after_enqueue_styles', 'GPAI_Elementor_Editor_Assets');
