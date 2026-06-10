<?php

function GPAI_Parent_MetaBox_register()
{
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $pt) {
        add_meta_box(
            'gpai_parent_box',
            'GPAI Parent',
            'GPAI_Parent_MetaBox_render',
            $pt,
            'side',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'GPAI_Parent_MetaBox_register');

function GPAI_Parent_MetaBox_render($post)
{
    wp_nonce_field('gpai_parent_save', 'gpai_parent_nonce');

    $parent_id = get_post_meta($post->ID, GPAI_KEY . '_PARENT', true);
    $content_independiente = get_post_meta($post->ID, GPAI_CONTENT_INDEPENDIENTE_META, true);

    if ($content_independiente === '') {
        $content_independiente = '1';
    }

    echo '<div style="padding:4px 0;">';

    if ($parent_id) {
        $parent_post = get_post($parent_id);
        if ($parent_post) {
            echo '<p><strong>Post padre:</strong> <a href="' . get_edit_post_link($parent_id) . '" target="_blank">' . esc_html($parent_post->post_title) . '</a></p>';
        } else {
            echo '<p><strong>Parent ID:</strong> ' . esc_html($parent_id) . ' (no existe)</p>';
        }

        echo '<p>';
        echo '<input type="hidden" name="gpai_parent_fields[content_independiente]" value="1">';
        echo '<label>';
        echo '<input type="checkbox" name="gpai_parent_fields[content_independiente]" value="0" ' . checked('0', $content_independiente, false) . '>';
        echo ' Cargar contenido del padre';
        echo '</label>';
        echo '</p>';
        echo '<p style="color:#666;font-size:12px;font-style:italic;">';
        echo 'Al activar, esta página mostrará el contenido del post padre en el frontend en lugar del suyo propio.';
        echo '</p>';
    } else {
        echo '<p>Este post no tiene un padre configurado.</p>';
        echo '<p style="color:#666;font-size:12px;font-style:italic;">';
        echo 'El parent se asigna automáticamente al generar variaciones desde "Procesar Contenido".';
        echo '</p>';
    }

    echo '</div>';
}

function GPAI_Parent_MetaBox_save($post_id)
{
    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['gpai_parent_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['gpai_parent_nonce'], 'gpai_parent_save')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['gpai_parent_fields']) && is_array($_POST['gpai_parent_fields'])) {
        $fields = $_POST['gpai_parent_fields'];

        if (isset($fields['content_independiente'])) {
            $value = $fields['content_independiente'] === '0' ? '0' : '1';
            update_post_meta($post_id, GPAI_CONTENT_INDEPENDIENTE_META, $value);
        }
    }
}
add_action('save_post', 'GPAI_Parent_MetaBox_save');
