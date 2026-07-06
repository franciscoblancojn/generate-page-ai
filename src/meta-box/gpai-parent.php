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
            echo '<p><strong>Post padre actual:</strong></p>';
            echo '<p><a href="' . esc_url(get_edit_post_link($parent_id)) . '" target="_blank">' . esc_html($parent_post->post_title) . '</a>';
            echo ' <a href="#" class="gpai-remove-parent" style="color:#a00;text-decoration:none;font-size:12px;" data-post-id="' . esc_attr($post->ID) . '">[Quitar]</a></p>';
        } else {
            echo '<p><strong>Parent ID:</strong> ' . esc_html($parent_id) . ' (no existe)</p>';
        }
    }

    echo '<p><label for="gpai_parent_search"><strong>Seleccionar post padre:</strong></label></p>';
    echo '<p>';
    echo '<input type="text" id="gpai_parent_search" class="gpai-parent-search" style="width:100%;" placeholder="Buscar posts por título..." autocomplete="off">';
    echo '<input type="hidden" name="gpai_parent_fields[parent_id]" id="gpai_parent_id" value="' . esc_attr($parent_id) . '">';
    echo '<div id="gpai_parent_results" style="display:none;max-height:200px;overflow-y:auto;border:1px solid #ddd;background:#fff;margin-top:4px;position:absolute;z-index:100;width:calc(100% - 4px);"></div>';
    echo '</p>';



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

        if (isset($fields['parent_id'])) {
            $parent_id = intval($fields['parent_id']);
            if ($parent_id > 0) {
                update_post_meta($post_id, GPAI_KEY . '_PARENT', $parent_id);
            } else {
                delete_post_meta($post_id, GPAI_KEY . '_PARENT');
                delete_post_meta($post_id, GPAI_CONTENT_INDEPENDIENTE_META);
            }
        }
    }
}
add_action('save_post', 'GPAI_Parent_MetaBox_save');

function GPAI_Parent_search_ajax()
{
    check_ajax_referer('gpai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }

    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $exclude = isset($_POST['exclude']) ? intval($_POST['exclude']) : 0;

    if (strlen($search) < 2) {
        wp_send_json_error('Search term too short');
    }

    $post_types = get_post_types(['public' => true], 'names');

    $args = [
        'post_type' => $post_types,
        'post_status' => 'publish',
        'posts_per_page' => 20,
        's' => $search,
        'orderby' => 'relevance',
        'order' => 'DESC',
        'post__not_in' => $exclude > 0 ? [$exclude] : [],
    ];

    $query = new WP_Query($args);
    $results = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_type_obj = get_post_type_object(get_post_type());
            $results[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'post_type' => $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type(),
                'edit_url' => get_edit_post_link(get_the_ID(), ''),
            ];
        }
        wp_reset_postdata();
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_gpai_search_parent_posts', 'GPAI_Parent_search_ajax');

function GPAI_Parent_admin_js()
{
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'post') {
        return;
    }
    ?>
<script>
jQuery(function($) {
    var searchTimeout;
    var $search = $('#gpai_parent_search');
    var $results = $('#gpai_parent_results');
    var $parentId = $('#gpai_parent_id');

    if (!$search.length) return;

    function gpai_select_parent(id, title) {
        $parentId.val(id);
        $results.hide().empty();
        if (title) {
            $search.val(title);
        } else {
            $search.val('');
        }
    }

    $search.on('input', function() {
        var term = $(this).val();
        clearTimeout(searchTimeout);

        if (term.length < 2) {
            $results.hide().empty();
            return;
        }

        $('#gpai_parent_results').css('position', 'absolute');

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'gpai_search_parent_posts',
                    search: term,
                    exclude: $('#post_ID').val() || 0,
                    nonce: gpaiParentNonce || ''
                },
                success: function(res) {
                    if (!res.success || !res.data.length) {
                        $results.html('<div style="padding:8px;color:#666;">Sin resultados</div>').show();
                        return;
                    }
                    var html = '';
                    $.each(res.data, function(i, post) {
                        html += '<div class="gpai-parent-result-item" data-id="' + post.id + '" data-title="' + $('<span>').text(post.title).html() + '" style="padding:6px 8px;cursor:pointer;border-bottom:1px solid #eee;">';
                        html += '<span style="font-weight:500;">' + $('<span>').text(post.title).html() + '</span>';
                        html += ' <span style="color:#666;font-size:11px;">(' + post.post_type + ')</span>';
                        html += '</div>';
                    });
                    $results.html(html).show();
                }
            });
        }, 300);
    });

    $results.on('click', '.gpai-parent-result-item', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        gpai_select_parent(id, title);
        $results.hide();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.gpai-parent-search, #gpai_parent_results').length) {
            $results.hide();
        }
    });

    $(document).on('click', '.gpai-remove-parent', function(e) {
        e.preventDefault();
        if (confirm('Quitar el post padre?')) {
            gpai_select_parent(0, '');
        }
    });
});
</script>
<?php
}
add_action('admin_footer', 'GPAI_Parent_admin_js');

function GPAI_Parent_localize_script()
{
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'post') {
        return;
    }
    wp_localize_script('jquery', 'gpaiParentNonce', wp_create_nonce('gpai_nonce'));
}
add_action('admin_enqueue_scripts', 'GPAI_Parent_localize_script');
