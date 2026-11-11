<?php

use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUCollapse;

$all_configs = get_option('GPAI_SITEMAP_CONFIGS', []);

$selected_sitemap = '';
if (isset($_POST['load_sitemap']) && !empty($_POST['sitemap_selector'])) {
    $selected_sitemap = sanitize_file_name($_POST['sitemap_selector']);
    update_user_meta(get_current_user_id(), 'gpai_last_sitemap', $selected_sitemap);
} elseif (isset($_POST['selected_sitemap']) && !empty($_POST['selected_sitemap'])) {
    $selected_sitemap = sanitize_file_name($_POST['selected_sitemap']);
    update_user_meta(get_current_user_id(), 'gpai_last_sitemap', $selected_sitemap);
} else {
    $saved = get_user_meta(get_current_user_id(), 'gpai_last_sitemap', true);
    if (!empty($saved) && isset($SITEMAPS[$saved])) {
        $selected_sitemap = $saved;
    }
}

$sitemap_config = [];
if (!empty($selected_sitemap) && isset($all_configs[$selected_sitemap])) {
    $sitemap_config = $all_configs[$selected_sitemap];
}

$defaults = [
    'enabled_posts' => [],
    'changefreq_page' => 'monthly',
    'priority_page' => '0.8',
    'changefreq_post' => 'weekly',
    'priority_post' => '0.9',
    'changefreq_default' => 'monthly',
    'priority_default' => '0.5',
    'include_images' => '1',
];
$sitemap_config = array_merge($defaults, $sitemap_config);

$generated_xml = '';

if (isset($_POST['save']) && $_POST['save'] == 'sitemap_config_save' && !empty($selected_sitemap)) {
    $enabled_posts = isset($_POST['enabled_posts']) ? array_map('intval', $_POST['enabled_posts']) : [];
    $config = [
        'enabled_posts' => $enabled_posts,
        'changefreq_page' => sanitize_text_field($_POST['changefreq_page'] ?? 'monthly'),
        'priority_page' => sanitize_text_field($_POST['priority_page'] ?? '0.8'),
        'changefreq_post' => sanitize_text_field($_POST['changefreq_post'] ?? 'weekly'),
        'priority_post' => sanitize_text_field($_POST['priority_post'] ?? '0.9'),
        'changefreq_default' => sanitize_text_field($_POST['changefreq_default'] ?? 'monthly'),
        'priority_default' => sanitize_text_field($_POST['priority_default'] ?? '0.5'),
        'include_images' => !empty($_POST['include_images']) ? '1' : '0',
    ];
    $all_configs[$selected_sitemap] = $config;
    update_option('GPAI_SITEMAP_CONFIGS', $all_configs);
    $sitemap_config = $config;
    $respond_urls = [
        "status" => "ok",
        "message" => "Configuracion guardada para " . esc_html($selected_sitemap) . " (" . count($enabled_posts) . " posts activos).",
        'data' => [],
    ];
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

$pages_by_segment = [];
$root_pages = [];
foreach ($posts_by_type['Paginas'] ?? [] as $page) {
    $permalink = get_permalink($page->ID);
    $path = parse_url($permalink, PHP_URL_PATH);
    $path = trim($path, '/');
    $segments = explode('/', $path);
    if (count($segments) > 1) {
        $segment = $segments[0];
        $pages_by_segment[$segment][] = $page;
    } else {
        $root_pages[] = $page;
    }
}
ksort($pages_by_segment);

?>
<?php FWURespond::render($respond_urls ?? null) ?>

<form method="post">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:1rem;padding:12px;background:#f0f0f1;border-radius:6px;">
        <label style="font-weight:600;">Sitemap:</label>
        <select name="sitemap_selector" style="flex:1;max-width:300px;">
            <option value="">— Seleccionar —</option>
            <?php foreach ($SITEMAPS as $filename => $sitemap): ?>
                <option value="<?= esc_attr($filename) ?>" <?= selected($filename, $selected_sitemap) ?>><?= esc_html($filename) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="load_sitemap" value="1" class="button">Cargar Sitemap</button>
    </div>

    <?php if (!empty($selected_sitemap)): ?>
        <input type="hidden" name="selected_sitemap" value="<?= esc_attr($selected_sitemap) ?>">

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
                                    <option value="<?= $freq ?>" <?= selected($sitemap_config['changefreq_page'], $freq) ?>><?= $freq ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="priority_page">
                                <?php foreach (['0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1.0'] as $p): ?>
                                    <option value="<?= $p ?>" <?= selected($sitemap_config['priority_page'], $p) ?>><?= $p ?></option>
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
                                    <option value="<?= $freq ?>" <?= selected($sitemap_config['changefreq_post'], $freq) ?>><?= $freq ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="priority_post">
                                <?php foreach (['0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1.0'] as $p): ?>
                                    <option value="<?= $p ?>" <?= selected($sitemap_config['priority_post'], $p) ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Otros tipos</strong></td>
                        <td>
                            <select name="changefreq_default">
                                <?php foreach (['always','hourly','daily','weekly','monthly','yearly','never'] as $freq): ?>
                                    <option value="<?= $freq ?>" <?= selected($sitemap_config['changefreq_default'], $freq) ?>><?= $freq ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="priority_default">
                                <?php foreach (['0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1.0'] as $p): ?>
                                    <option value="<?= $p ?>" <?= selected($sitemap_config['priority_default'], $p) ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="hidden" name="include_images" value="0">
                                <input type="checkbox" name="include_images" value="1" <?= checked($sitemap_config['include_images'], '1', false) ?>>
                                Agregar <code>&lt;image:image&gt;</code> (imágenes destacadas, del contenido y galerías)
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </details>

        <?php
        $enabled_posts = $sitemap_config['enabled_posts'];
        foreach ($posts_by_type as $label => $posts):
        ?>
            <?php
            $collapse_title = '<div class="content-title-btn" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding-right:2rem;">
                <strong>' . esc_html($label) . '</strong>
                <span style="font-weight:400;font-size:12px;color:#666;">' . count($posts) . ' posts</span>
            </div>';

            if ($label === 'Paginas') {
                $inner = '';
                if (!empty($root_pages)) {
                    $sub_title = '<div class="content-title-btn" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding-right:2rem;">
                        <strong style="padding-left:1rem;">Raíz</strong>
                        <span style="font-weight:400;font-size:12px;color:#666;">' . count($root_pages) . ' páginas</span>
                    </div>';
                    $inner .= FWUCollapse::html($sub_title, GPAI_Table_Post_By_Url($root_pages, $enabled_posts), false);
                }
                foreach ($pages_by_segment as $segment => $segment_pages) {
                    $sub_title = '<div class="content-title-btn" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding-right:2rem;">
                        <strong style="padding-left:1rem;">' . esc_html($segment) . '</strong>
                        <span style="font-weight:400;font-size:12px;color:#666;">' . count($segment_pages) . ' páginas</span>
                    </div>';
                    $inner .= FWUCollapse::html($sub_title, GPAI_Table_Post_By_Url($segment_pages, $enabled_posts), false);
                }
                FWUCollapse::render($collapse_title, $inner, false);
            } else {
                $table = GPAI_Table_Post_By_Url($posts, $enabled_posts);
                FWUCollapse::render($collapse_title, $table, false);
            }
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

        <div id="gpai-sitemap-generate-result" style="margin-bottom:1rem;display:none;">
            <div class="content-btn" style="margin-bottom:0.5rem;">
                <strong>XML Generado</strong>
                <button type="button" id="gpai-sitemap-save-xml-btn" class="button button-primary" data-sitemap="<?= esc_attr($selected_sitemap) ?>">Guardar XML</button>
                <span id="gpai-sitemap-save-xml-status" style="margin-left:0.5rem;font-size:12px;"></span>
            </div>
            <textarea id="gpai-sitemap-generate-xml" class="large-text code" style="min-height:300px;font-family:monospace;font-size:11px;" rows="20" readonly></textarea>
        </div>

        <div class="content-btn" style="margin-bottom:1rem;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <button type="submit" name="save" value="sitemap_config_save" class="button button-primary">Guardar Configuracion</button>
            <button type="button" id="gpai-sitemap-save-generate" class="button button-primary" data-sitemap="<?= esc_attr($selected_sitemap) ?>">Guardar y Generar XML</button>
            <span id="gpai-sitemap-save-generate-status" style="font-size:13px;color:#666;"></span>
            <?php
            $total = 0;
            foreach ($posts_by_type as $label => $posts) $total += count($posts);
            echo '<span style="font-size:13px;color:#666;margin-left:auto;">' . $total . ' posts disponibles</span>';
            ?>
        </div>
    <?php endif; ?>
</form>

<script>
    document.addEventListener('change', function(e) {
        const checkbox = e.target.closest('.gpai-toggle-type');
        if (!checkbox) return;
        const typeCollapse = checkbox.closest('details');
        const checkboxes = typeCollapse.querySelectorAll('input[name="enabled_posts[]"]');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#gpai-sitemap-save-generate');
        if (!btn) return;

        const form = btn.closest('form');
        const statusEl = document.getElementById('gpai-sitemap-save-generate-status');
        const resultDiv = document.getElementById('gpai-sitemap-generate-result');
        const resultXml = document.getElementById('gpai-sitemap-generate-xml');

        btn.disabled = true;
        statusEl.style.color = '';
        statusEl.textContent = 'Guardando y generando...';

        var fields = {};
        form.querySelectorAll('[name^="changefreq_"], [name^="priority_"], [name="selected_sitemap"]').forEach(function(input) {
            fields[input.name] = input.value;
        });
        fields.include_images = form.querySelector('[name="include_images"][type="checkbox"]').checked ? '1' : '0';

        var enabledPosts = [];
        form.querySelectorAll('input[name="enabled_posts[]"]:checked').forEach(function(cb) {
            enabledPosts.push(cb.value);
        });

        var formData = new FormData();
        formData.append('action', 'gpai_sitemap_save_generate');
        formData.append('sitemap_name', btn.dataset.sitemap.replace('.xml', ''));
        Object.keys(fields).forEach(function(k) {
            formData.append(k, fields[k]);
        });
        enabledPosts.forEach(function(id) {
            formData.append('enabled_posts[]', id);
        });

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (data.success) {
                    statusEl.style.color = '#00a32a';
                    statusEl.textContent = data.data.message || '✓ Completado';
                    if (data.data.content) {
                        resultXml.value = data.data.content;
                        resultDiv.style.display = 'block';
                    }
                } else {
                    statusEl.style.color = '#d63638';
                    statusEl.textContent = '✗ ' + (data.data || 'Error');
                }
                setTimeout(function() {
                    if (statusEl.textContent.startsWith('✓')) {
                        statusEl.textContent = '';
                    }
                }, 5000);
            })
            .catch(function() {
                btn.disabled = false;
                statusEl.style.color = '#d63638';
                statusEl.textContent = '✗ Error de conexión';
            });
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#gpai-sitemap-save-xml-btn');
        if (!btn) return;

        const statusEl = document.getElementById('gpai-sitemap-save-xml-status');
        const xmlTextarea = document.getElementById('gpai-sitemap-generate-xml');

        btn.disabled = true;
        statusEl.textContent = 'Guardando...';

        var formData = new FormData();
        formData.append('action', 'gpai_sitemap_save_xml');
        formData.append('sitemap_name', btn.dataset.sitemap.replace('.xml', ''));
        formData.append('xml_content', xmlTextarea.value);

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (data.success) {
                    statusEl.style.color = '#00a32a';
                    statusEl.textContent = '✓ Guardado';
                } else {
                    statusEl.style.color = '#d63638';
                    statusEl.textContent = '✗ ' + (data.data || 'Error');
                }
                setTimeout(function() { statusEl.textContent = ''; }, 4000);
            })
            .catch(function() {
                btn.disabled = false;
                statusEl.style.color = '#d63638';
                statusEl.textContent = '✗ Error de conexión';
            });
    });
</script>
