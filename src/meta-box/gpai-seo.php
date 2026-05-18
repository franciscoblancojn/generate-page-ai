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
            'advanced',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'GPAI_SEO_MetaBox_register');

function GPAI_SEO_MetaBox_render($post)
{
    wp_nonce_field('gpai_seo_save', 'gpai_seo_nonce');

    $allFields = GPAI_SEO::getFields();
    $groups = GPAI_SEO::getGroups();
    $values = GPAI_SEO::GET($post->ID);

    echo '<div style="display:flex;flex-direction:column;gap:12px;">';

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
            $type = GPAI_SEO_MetaBox_getFieldType($key);

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
                    echo '<textarea id="gpai_seo_' . esc_attr($key) . '" name="gpai_seo_fields[' . esc_attr($key) . ']" class="large-text code" style="min-height:60px;">' . esc_textarea($value) . '</textarea>';
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

    echo '</div>';
}

function GPAI_SEO_MetaBox_save($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_MetaBox_save',
            'post_id' => $post_id,
            'step' => 'exit_autosave',
        ]);
        return;
    }

    if (!isset($_POST['gpai_seo_nonce'])) {
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_MetaBox_save',
            'post_id' => $post_id,
            'step' => 'exit_no_nonce',
            'post_keys' => array_keys($_POST),
        ]);
        return;
    }

    if (!wp_verify_nonce($_POST['gpai_seo_nonce'], 'gpai_seo_save')) {
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_MetaBox_save',
            'post_id' => $post_id,
            'step' => 'exit_nonce_invalid',
            'nonce' => $_POST['gpai_seo_nonce'],
        ]);
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_MetaBox_save',
            'post_id' => $post_id,
            'step' => 'exit_no_cap',
        ]);
        return;
    }

    if (isset($_POST['gpai_seo_fields']) && is_array($_POST['gpai_seo_fields'])) {
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_MetaBox_save',
            'post_id' => $post_id,
            'step' => 'calling_SET',
            'fields' => $_POST['gpai_seo_fields'],
        ]);
        GPAI_SEO::SET($post_id, $_POST['gpai_seo_fields']);
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_MetaBox_save',
            'post_id' => $post_id,
            'step' => 'SET_complete',
        ]);
    } else {
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_MetaBox_save',
            'post_id' => $post_id,
            'step' => 'exit_no_fields',
            'gpai_seo_fields_raw' => $_POST['gpai_seo_fields'] ?? 'NOT_SET',
            'post_keys' => array_keys($_POST),
        ]);
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
