<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

function GPAI_SEO_MetaBox_register()
{
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $pt) {
        add_meta_box(
            'gpai_seo_box',
            'Gpai SEO',
            'GPAI_SEO_MetaBox_render',
            $pt,
            'normal',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'GPAI_SEO_MetaBox_register');

function GPAI_SEO_MetaBox_render($post)
{
    wp_nonce_field('gpai_seo_save', 'gpai_seo_nonce');

    $allFields = GPAI_SEO::getFields();
    $groups    = GPAI_SEO::getGroups();
    $values    = GPAI_SEO::GET($post->ID);
    $ajax_nonce = wp_create_nonce('gpai_seo_ajax_' . $post->ID);
    $generate_nonce = wp_create_nonce('gpai_seo_generate_' . $post->ID);

    echo '<div id="gpai-seo-box" style="display:flex;flex-direction:column;gap:12px;" data-post-id="' . esc_attr($post->ID) . '" data-nonce="' . esc_attr($ajax_nonce) . '">';

    foreach ($groups as $groupName => $fieldKeys) {
        $hasAny = false;
        foreach ($fieldKeys as $k) {
            if (isset($allFields[$k])) $hasAny = true;
        }
        if (!$hasAny) continue;

        $open = ($groupName === 'Principales');
        echo '<details ' . ($open ? 'open' : '') . ' style="border:1px solid #ddd;border-radius:6px;background:#fafafa;">';
        echo '<summary style="padding:8px 12px;font-weight:600;cursor:pointer;background:#f0f0f1;border-radius:6px 6px 0 0;user-select:none;">' . esc_html($groupName) . '</summary>';
        echo '<div style="padding:12px;">';
        echo '<table class="form-table" style="margin:0;">';

        foreach ($fieldKeys as $key) {
            if (!isset($allFields[$key])) continue;
            $label = $allFields[$key];
            $value = $values[$key] ?? '';
            $type  = GPAI_SEO_MetaBox_getFieldType($key);

            echo '<tr>';
            echo '<th scope="row" style="width:180px;padding:6px 10px 6px 0;">';
            echo '<label for="gpai_seo_' . esc_attr($key) . '">' . esc_html($label) . '</label>';
            echo '</th>';
            echo '<td style="padding:6px 0;">';

            switch ($type) {
                case 'checkbox':
                    echo '<input type="hidden" name="gpai_seo_fields[' . esc_attr($key) . ']" value="0">';
                    echo '<input type="checkbox" id="gpai_seo_' . esc_attr($key) . '" name="gpai_seo_fields[' . esc_attr($key) . ']" value="1" ' . checked('1', $value, false) . '>';
                    break;
                case 'select':
                    echo '<select id="gpai_seo_' . esc_attr($key) . '" name="gpai_seo_fields[' . esc_attr($key) . ']" class="regular-text">';
                    $options = GPAI_SEO_MetaBox_getFieldOptions($key);
                    foreach ($options as $optVal => $optLabel) {
                        echo '<option value="' . esc_attr($optVal) . '" ' . selected($optVal, $value, false) . '>' . esc_html($optLabel) . '</option>';
                    }
                    echo '</select>';
                    break;
                case 'textarea':
                    $minHeight = ($key === 'gpai_wpseo_schema_extra_json') ? '200px' : '60px';
                    echo '<textarea id="gpai_seo_' . esc_attr($key) . '" name="gpai_seo_fields[' . esc_attr($key) . ']" class="large-text code" style="min-height:' . $minHeight . ';">' . esc_textarea($value) . '</textarea>';
                    break;
                default:
                    echo '<input type="' . $type . '" id="gpai_seo_' . esc_attr($key) . '" name="gpai_seo_fields[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text" style="width:100%;">';
                    break;
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';
        echo '</details>';
    }

    echo '<div style="padding:4px 0;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">';
    echo '<button type="button" id="gpai-seo-save-btn" class="button button-primary">Guardar SEO</button>';
    echo '<button type="button" id="gpai-seo-generate-btn" class="button button-primary" data-post-id="' . esc_attr($post->ID) . '" data-nonce="' . esc_attr($generate_nonce) . '">Generar SEO con IA</button>';
    echo '<a href="https://validator.schema.org/#url=' . urlencode(get_permalink($post->ID)) . '" target="_blank" class="button">Validar SEO</a>';
    echo '<span id="gpai-seo-save-status" style="font-style:italic;font-size:13px;"></span>';
    echo '</div>';

    echo '</div>';

    static $script_registered = false;
    if (!$script_registered) {
        $script_registered = true;
        add_action('admin_footer', 'GPAI_SEO_MetaBox_script');
    }
}

function GPAI_SEO_MetaBox_script()
{
?>
<script>
(function () {
    var box = document.getElementById('gpai-seo-box');
    var btn = document.getElementById('gpai-seo-save-btn');
    if (!box || !btn) return;

    btn.addEventListener('click', function () {
        var postId = box.dataset.postId;
        var nonce  = box.dataset.nonce;
        var status = document.getElementById('gpai-seo-save-status');
        var fields = {};

        box.querySelectorAll('[name^="gpai_seo_fields["]').forEach(function (input) {
            var match = input.name.match(/gpai_seo_fields\[(.+)\]/);
            if (!match) return;
            var key = match[1];
            if (input.type === 'checkbox') {
                fields[key] = input.checked ? '1' : '0';
            } else {
                fields[key] = input.value;
            }
        });

        btn.disabled = true;
        status.style.color = '';
        status.textContent = 'Guardando...';

        var formData = new FormData();
        formData.append('action', 'gpai_seo_save');
        formData.append('nonce', nonce);
        formData.append('post_id', postId);
        Object.keys(fields).forEach(function (k) {
            formData.append('fields[' + k + ']', fields[k]);
        });

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                if (data.success) {
                    status.style.color = '#00a32a';
                    status.textContent = '✓ Guardado correctamente';
                } else {
                    status.style.color = '#d63638';
                    status.textContent = '✗ ' + (data.data || 'Error al guardar');
                }
                setTimeout(function () { status.textContent = ''; }, 3000);
            })
            .catch(function () {
                btn.disabled = false;
                status.style.color = '#d63638';
                status.textContent = '✗ Error de conexión';
            });
    });
    })();

    var generateBtn = document.getElementById('gpai-seo-generate-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function () {
            var postId = generateBtn.dataset.postId;
            var nonce = generateBtn.dataset.nonce;
            var status = document.getElementById('gpai-seo-save-status');

            generateBtn.disabled = true;
            if (status) { status.style.color = ''; status.textContent = 'Generando SEO...'; }

            var formData = new FormData();
            formData.append('action', 'gpai_seo_generate');
            formData.append('post_id', postId);
            formData.append('nonce', nonce);

            fetch(ajaxurl, { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        if (status) { status.style.color = '#00a32a'; status.textContent = '✓ SEO generado. Recargando...'; }
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        generateBtn.disabled = false;
                        if (status) { status.style.color = '#d63638'; status.textContent = '✗ ' + (data.data || 'Error'); }
                    }
                })
                .catch(function () {
                    generateBtn.disabled = false;
                    if (status) { status.style.color = '#d63638'; status.textContent = '✗ Error de conexión'; }
                });
        });
    }
</script>
<?php
}

function GPAI_SEO_save_ajax()
{
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $nonce   = $_POST['nonce'] ?? '';
    $fields  = isset($_POST['fields']) && is_array($_POST['fields']) ? $_POST['fields'] : [];

    if (!$post_id || !wp_verify_nonce($nonce, 'gpai_seo_ajax_' . $post_id)) {
        wp_send_json_error('Nonce inválido.');
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Sin permisos.');
        return;
    }

    if (empty($fields)) {
        wp_send_json_error('No se recibieron campos.');
        return;
    }

    FWUSystemLog::add(GPAI_KEY, [
        'type'    => 'gpai_seo_save_ajax',
        'post_id' => $post_id,
        'fields'  => $fields,
    ]);

    GPAI_SEO::SET($post_id, $fields);
    wp_send_json_success(['message' => 'Guardado correctamente.']);
}
add_action('wp_ajax_gpai_seo_save', 'GPAI_SEO_save_ajax');

function GPAI_SEO_MetaBox_save($post_id)
{
    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['gpai_seo_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['gpai_seo_nonce'], 'gpai_seo_save')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['gpai_seo_fields']) && is_array($_POST['gpai_seo_fields'])) {
        GPAI_SEO::SET($post_id, $_POST['gpai_seo_fields']);
    }
}
add_action('save_post', 'GPAI_SEO_MetaBox_save');

function GPAI_SEO_MetaBox_getFieldType($key)
{
    $textFields = [
        'gpai_wpseo_title',
        'gpai_wpseo_focuskw',
        'gpai_wpseo_canonical',
        'gpai_wpseo_bctitle',
        'gpai_wpseo_redirect',
        'gpai_wpseo_meta-robots-adv',
        'gpai_wpseo_opengraph-title',
        'gpai_wpseo_opengraph-image',
        'gpai_wpseo_opengraph-url',
        'gpai_wpseo_twitter-title',
        'gpai_wpseo_twitter-image',
        'gpai_wpseo_schema_page_type',
        'gpai_wpseo_schema_article_type',
    ];
    $textareaFields = [
        'gpai_wpseo_metadesc',
        'gpai_wpseo_focuskeywords',
        'gpai_wpseo_opengraph-description',
        'gpai_wpseo_twitter-description',
        'gpai_wpseo_schema_extra_json',
    ];
    $checkboxFields = [
        'gpai_wpseo_is_cornerstone',
        'gpai_wpseo_meta-robots-noarchive',
        'gpai_wpseo_meta-robots-nosnippet',
        'gpai_wpseo_meta-robots-noimageindex',
    ];
    $selectFields = [
        'gpai_wpseo_meta-robots-noindex',
        'gpai_wpseo_meta-robots-nofollow',
    ];
    $numberFields = [
        'gpai_wpseo_opengraph-image-id',
    ];

    if (in_array($key, $textFields)) return 'text';
    if (in_array($key, $textareaFields)) return 'textarea';
    if (in_array($key, $checkboxFields)) return 'checkbox';
    if (in_array($key, $selectFields)) return 'select';
    if (in_array($key, $numberFields)) return 'number';
    return 'text';
}

function GPAI_SEO_MetaBox_getFieldOptions($key)
{
    switch ($key) {
        case 'gpai_wpseo_meta-robots-noindex':
            return ['0' => 'Index (predeterminado)', '1' => 'No Index'];
        case 'gpai_wpseo_meta-robots-nofollow':
            return ['0' => 'Follow (predeterminado)', '1' => 'No Follow'];
        default:
            return [];
    }
}
