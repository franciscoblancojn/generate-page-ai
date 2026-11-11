<?php

function GPAI_Edit_Assets()
{
    if (!is_user_logged_in() || !current_user_can('manage_options')) return;
    if (!isset($_GET['GPAI_EDIT'])) return;
    if (is_admin()) return;
    if (wp_doing_ajax()) return;

    wp_enqueue_style(
        'gpai-edit',
        GPAI_URL . 'src/css/gpai-edit.css',
        [],
        GPAI_get_version() . ".0"
    );

    wp_enqueue_script(
        'gpai-edit',
        GPAI_URL . 'src/js/gpai-edit.js',
        ['jquery'],
        GPAI_get_version() . ".0",
        true
    );

    $post_id = get_the_ID();
    if (!$post_id) {
        $post_id = get_queried_object_id();
    }

    wp_localize_script('gpai-edit', 'gpaiEdit', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gpai_elementor_nonce'),
        'post_id' => intval($post_id),
        'custom_fields_disabled' => isset($_GET['GPAI_CUSTOM_FIELDS_DISABLE']),
    ]);
}

add_action('wp_enqueue_scripts', 'GPAI_Edit_Assets');
