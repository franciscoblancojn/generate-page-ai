<?php

$option_key = 'GPAI_SITEMAP_URLS';
$config_key = 'GPAI_SITEMAP_URLS_CONFIG';
$enabled_posts = get_option($option_key, []);
$urls_config = get_option($config_key, []);
$generated_xml = '';

$defaults = [
    'changefreq_page' => 'monthly',
    'priority_page' => '0.8',
    'changefreq_post' => 'weekly',
    'priority_post' => '0.9',
    'changefreq_default' => 'monthly',
    'priority_default' => '0.5',
];
$urls_config = array_merge($defaults, $urls_config);

function get_changefreq($post, $urls_config)
{
    if ($post->post_type === 'page') return $urls_config['changefreq_page'];
    if ($post->post_type === 'post') return $urls_config['changefreq_post'];
    return $urls_config['changefreq_default'];
}

function get_priority($post, $urls_config)
{
    if ($post->post_type === 'page') {
        $front_id = (int) get_option('page_on_front');
        if ($post->ID === $front_id) return '1.0';
        return $urls_config['priority_page'];
    }
    if ($post->post_type === 'post') return $urls_config['priority_post'];
    return $urls_config['priority_default'];
}

if (isset($_POST['save']) && $_POST['save'] == 'sitemap_urls_save') {
    $enabled_posts = isset($_POST['enabled_posts']) ? array_map('intval', $_POST['enabled_posts']) : [];
    update_option($option_key, $enabled_posts);

    $urls_config['changefreq_page'] = sanitize_text_field($_POST['changefreq_page'] ?? 'monthly');
    $urls_config['priority_page'] = sanitize_text_field($_POST['priority_page'] ?? '0.8');
    $urls_config['changefreq_post'] = sanitize_text_field($_POST['changefreq_post'] ?? 'weekly');
    $urls_config['priority_post'] = sanitize_text_field($_POST['priority_post'] ?? '0.9');
    $urls_config['changefreq_default'] = sanitize_text_field($_POST['changefreq_default'] ?? 'monthly');
    $urls_config['priority_default'] = sanitize_text_field($_POST['priority_default'] ?? '0.5');
    update_option($config_key, $urls_config);

    $respond_urls = [
        "status" => "ok",
        "message" => "Configuracion guardada (" . count($enabled_posts) . " posts activos).",
        'data' => [],
    ];
}

if (isset($_POST['save']) && $_POST['save'] == 'sitemap_urls_generate') {
    $enabled_posts = isset($_POST['enabled_posts']) ? array_map('intval', $_POST['enabled_posts']) : [];
    $xml_lines = [];
    $skipped = [];

    foreach ($enabled_posts as $post_id) {
        $post = get_post($post_id);
        if (!$post) continue;
        $permalink = get_permalink($post_id);
        if (!$permalink) continue;

        if (strpos($permalink, '?page_id=') !== false) {
            $skipped[] = "{$permalink} (?page_id)";
            continue;
        }

        $response = wp_remote_head($permalink, [
            'timeout' => 5,
            'blocking' => true,
        ]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) === 404) {
            $skipped[] = "{$permalink} (404)";
            continue;
        }

        $lastmod = get_the_modified_date('Y-m-d', $post_id);
        $changefreq = get_changefreq($post, $urls_config);
        $priority = get_priority($post, $urls_config);
        $xml_lines[] = "\t<url>\n\t\t<loc>" . esc_url($permalink) . "</loc>\n\t\t<lastmod>{$lastmod}</lastmod>\n\t\t<changefreq>{$changefreq}</changefreq>\n\t\t<priority>{$priority}</priority>\n\t</url>";
    }

    if (!empty($xml_lines)) {
        $generated_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . implode("\n", $xml_lines) . "\n</urlset>";
        $message = "XML generado con " . count($xml_lines) . " URLs.";
        if (!empty($skipped)) {
            $message .= ' Omitidas: ' . implode(', ', $skipped);
        }
        $respond_urls = [
            "status" => "ok",
            "message" => $message,
            'data' => [],
        ];
    } else {
        $respond_urls = [
            "status" => "error",
            "message" => "No hay posts activos seleccionados.",
            'data' => [],
        ];
    }
}

$posts_by_type = [];
$order_keys = ['Paginas' => 0, 'Posts' => 1];

$pages_query = new WP_Query([
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
]);
if ($pages_query->have_posts()) {
    $posts_by_type['Paginas'] = $pages_query->posts;
}

$post_types = get_post_types(['public' => true, 'publicly_queryable' => true], 'objects');
foreach ($post_types as $pt) {
    if ($pt->name === 'attachment' || $pt->name === 'page') continue;
    $query = new WP_Query([
        'post_type' => $pt->name,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    if ($query->have_posts()) {
        $label = $pt->label ?? $pt->name;
        $posts_by_type[$label] = $query->posts;
        if (!isset($order_keys[$label])) {
            $order_keys[$label] = count($order_keys);
        }
    }
}
uksort($posts_by_type, function ($a, $b) use ($order_keys) {
    return ($order_keys[$a] ?? 99) - ($order_keys[$b] ?? 99);
});

?>
<?= GPAI_Respond($respond_urls ?? null) ?>

<form method="post">
    <input type="hidden" name="save" value="sitemap_urls_save">

    <details style="margin-bottom:1rem;" open>
        <summary style="display:flex;cursor:pointer;padding:8px 12px;background:#f0f0f1;font-weight:600;">Configuracion de Frecuencia y Prioridad</summary>
        <div style="padding:12px;border:1px solid #dcdcde;border-top:none;">
            <table class="form-table" style="margin:0;">
                <tr>
                    <th style="width:180px;">Tipo</th>
                    <th style="width:150px;">Frecuencia (changefreq)</th>
                    <th>Prioridad (priority)</th>
                </tr>
                <tr>
                    <td><strong>Paginas</strong></td>
                    <td>
                        <select name="changefreq_page">
                            <?php foreach (['always','hourly','daily','weekly','monthly','yearly','never'] as $freq): ?>
                                <option value="<?= $freq ?>" <?= selected($urls_config['changefreq_page'], $freq) ?>><?= $freq ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="priority_page">
                            <?php foreach (['0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1.0'] as $p): ?>
                                <option value="<?= $p ?>" <?= selected($urls_config['priority_page'], $p) ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span style="margin-left:1rem;font-size:11px;color:#666;">(Homepage usa 1.0)</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Posts</strong></td>
                    <td>
                        <select name="changefreq_post">
                            <?php foreach (['always','hourly','daily','weekly','monthly','yearly','never'] as $freq): ?>
                                <option value="<?= $freq ?>" <?= selected($urls_config['changefreq_post'], $freq) ?>><?= $freq ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="priority_post">
                            <?php foreach (['0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1.0'] as $p): ?>
                                <option value="<?= $p ?>" <?= selected($urls_config['priority_post'], $p) ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><strong>Otros tipos</strong></td>
                    <td>
                        <select name="changefreq_default">
                            <?php foreach (['always','hourly','daily','weekly','monthly','yearly','never'] as $freq): ?>
                                <option value="<?= $freq ?>" <?= selected($urls_config['changefreq_default'], $freq) ?>><?= $freq ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="priority_default">
                            <?php foreach (['0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1.0'] as $p): ?>
                                <option value="<?= $p ?>" <?= selected($urls_config['priority_default'], $p) ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </details>

    <?php foreach ($posts_by_type as $label => $posts): ?>
        <?php
        $collapse_title = '<div class="content-title-btn" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding-right:2rem;">
            <strong>' . esc_html($label) . '</strong>
            <span style="font-weight:400;font-size:12px;color:#666;">' . count($posts) . ' posts</span>
        </div>';

        $table = GPAI_Table_Post_By_Url($posts, $enabled_posts);

        echo GPAI_Collapse($collapse_title, $table, false);
        ?>
    <?php endforeach; ?>

    <?php if (!empty($generated_xml)): ?>
        <div style="margin-bottom:1rem;">
            <div class="content-btn" style="margin-bottom:0.5rem;">
                <strong>XML Generado</strong>
                <button type="button" class="button" onclick="gpaiCopyToClipboard(this)">Copiar XML</button>
            </div>
            <textarea class="large-text code" style="min-height:300px;font-family:monospace;font-size:11px;" rows="20" readonly><?= esc_textarea($generated_xml) ?></textarea>
        </div>
    <?php endif; ?>

    <div class="content-btn" style="margin-bottom:1rem;">
        <button type="submit" class="button button-primary">Guardar Configuracion</button>
        <!-- <button type="submit" name="save" value="sitemap_urls_generate" class="button">Generar URLs</button> -->
        <span style="font-size:13px;color:#666;">
            <?php
            $total = 0;
            foreach ($posts_by_type as $label => $posts) $total += count($posts);
            echo $total; ?> posts disponibles
        </span>
    </div>
</form>

<script>
    document.addEventListener('change', function(e) {
        const checkbox = e.target.closest('.gpai-toggle-type');
        if (!checkbox) return;
        const typeCollapse = checkbox.closest('details');
        const checkboxes = typeCollapse.querySelectorAll('input[name="enabled_posts[]"]');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
    });

    function gpaiCopyToClipboard(btn) {
        const textarea = btn.closest('div').parentElement.querySelector('textarea');
        if (!textarea) return;
        textarea.select();
        textarea.setSelectionRange(0, 9999999);
        navigator.clipboard.writeText(textarea.value).then(function() {
            const orig = btn.textContent;
            btn.textContent = '✓ Copiado';
            setTimeout(function() { btn.textContent = orig; }, 2000);
        }).catch(function() {
            document.execCommand('copy');
            const orig = btn.textContent;
            btn.textContent = '✓ Copiado';
            setTimeout(function() { btn.textContent = orig; }, 2000);
        });
    }
</script>
