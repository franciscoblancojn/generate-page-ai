<?php

function GPAI_Box_MetaBox_register()
{
    $post_types = get_post_types(array('public' => true), 'names');
    foreach ($post_types as $pt) {
        add_meta_box(
            'gpai_box',
            'GPAI',
            'GPAI_Box_MetaBox_render',
            $pt,
            'side',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'GPAI_Box_MetaBox_register');

function GPAI_Box_MetaBox_render($post)
{
    $permalink = get_permalink($post->ID);
    $edit_url = $permalink . '?STPA_DISABLE&GPAI_EDIT&GPAI_CUSTOM_FIELDS_DISABLE';

    echo '<div style="padding:4px 0;">';
    echo '<p style="margin-top:0;">Editar los campos personalizados y datos SEO directamente en la pagina.</p>';
    echo '<p><a href="' . esc_url($edit_url) . '" target="_blank" class="button button-primary" style="width:100%;text-align:center;">Editar Campos en Pagina</a></p>';
    echo '</div>';
}
