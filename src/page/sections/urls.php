<?php

$option_key = 'GPAI_SITEMAP_URLS';
$enabled_posts = get_option($option_key, []);
$generated_xml = '';

if (isset($_POST['save']) && $_POST['save'] == 'sitemap_urls_save') {
    $enabled_posts = isset($_POST['enabled_posts']) ? array_map('intval', $_POST['enabled_posts']) : [];
    update_option($option_key, $enabled_posts);
    $respond_urls = [
        "status" => "ok",
        "message" => "Configuracion guardada (" . count($enabled_posts) . " posts activos).",
        'data' => [],
    ];
}

if (isset($_POST['save']) && $_POST['save'] == 'sitemap_urls_generate') {
    $enabled_posts = isset($_POST['enabled_posts']) ? array_map('intval', $_POST['enabled_posts']) : [];
    $site_url = trailingslashit(get_site_url());
    $xml_lines = [];

    foreach ($enabled_posts as $post_id) {
        $permalink = get_permalink($post_id);
        if ($permalink) {
            $xml_lines[] = "\t<url>\n\t\t<loc>" . esc_url($permalink) . "</loc>\n\t</url>";
        }
    }

    if (!empty($xml_lines)) {
        $generated_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . implode("\n", $xml_lines) . "\n</urlset>";
        $respond_urls = [
            "status" => "ok",
            "message" => "XML generado con " . count($xml_lines) . " URLs.",
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

    <?php foreach ($posts_by_type as $label => $posts): ?>
        <?php
        $collapse_title = '<div class="content-title-btn" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding-right:2rem;">
            <strong>' . esc_html($label) . '</strong>
            <span style="font-weight:400;font-size:12px;color:#666;">' . count($posts) . ' posts</span>
        </div>';

        $table = GPAI_Table_Post_By_Url($posts,$enabled_posts);


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
        <button type="submit" name="save" value="sitemap_urls_generate" class="button">Generar URLs</button>
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
            setTimeout(function() {
                btn.textContent = orig;
            }, 2000);
        }).catch(function() {
            document.execCommand('copy');
            const orig = btn.textContent;
            btn.textContent = '✓ Copiado';
            setTimeout(function() {
                btn.textContent = orig;
            }, 2000);
        });
    }
</script>