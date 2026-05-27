<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$respond_sitemaps = null;

if (isset($_POST['save']) && $_POST['save'] == "sitemap_update") {
    $filename = $_POST['sitemap_file'] ?? '';
    $content = $_POST['sitemap_content'] ?? '';

    if (!empty($filename)) {
        $saved = $GPAI_USE_DATA_SITEMAPS->saveSitemap($filename, $content);
        if ($saved) {
            $respond_sitemaps = [
                "status" => "ok",
                "message" => "Site Map guardado.",
                'data' => [],
            ];
        } else {
            $respond_sitemaps = [
                "status" => "error",
                "message" => "Error al guardar el Site Map.",
                'data' => [],
            ];
        }
    }
    FWUSystemLog::add(GPAI_KEY, [
        'type' => "save_sitemap",
        'data' => $_POST
    ]);
    $SITEMAPS = $GPAI_USE_DATA_SITEMAPS->getSitemaps();
}

if (isset($_POST['save']) && $_POST['save'] == "sitemap_delete") {
    $filename = $_POST['sitemap_file'] ?? '';
    if (!empty($filename)) {
        $deleted = $GPAI_USE_DATA_SITEMAPS->deleteSitemap($filename);
        if ($deleted) {
            $respond_sitemaps = [
                "status" => "ok",
                "message" => "Site Map eliminado.",
                'data' => [],
            ];
        } else {
            $respond_sitemaps = [
                "status" => "error",
                "message" => "Error al eliminar el Site Map.",
                'data' => [],
            ];
        }
    }
    $SITEMAPS = $GPAI_USE_DATA_SITEMAPS->getSitemaps();
}

?>
<?= GPAI_Respond($respond_sitemaps) ?>

<?php if (empty($SITEMAPS)): ?>
    <p>No hay archivos XML de Site Maps en la raiz de WordPress. Ve a la pestaña "Crear Site Map" para crear uno.</p>
<?php else: ?>
    <?php foreach ($SITEMAPS as $filename => $sitemap): ?>
        <?php
        $sitemap_url = home_url($filename);

        $collapse_title = '<div class="content-title-btn" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding-right:2rem;">
            <strong>' . esc_html($filename) . '</strong>
            <span style="font-weight:400;font-size:12px;color:#666;">
                ' . size_format($sitemap['size']) . ' — ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $sitemap['modified']) . '
            </span>
        </div>';

        $collapse_content = '
        <form method="post">
            <input type="hidden" name="save" value="sitemap_update">
            <input type="hidden" name="sitemap_file" value="' . esc_attr($filename) . '">
            <table class="form-table">
                <tr>
                    <th scope="row">Archivo</th>
                    <td>
                        <div style="display:flex;gap:.5rem;align-items:center;">
                            <code>' . esc_html($filename) . '</code>
                            <a href="' . esc_url($sitemap_url) . '" target="_blank" class="button" style="margin-left:1rem;">Ver</a>
                            <a href="' . esc_url($sitemap_url) . '" download class="button">Descargar</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Contenido XML</th>
                    <td>
                        <textarea name="sitemap_content" class="large-text code" style="min-height:250px;font-family:monospace;" rows="15">' . esc_textarea($sitemap['content']) . '</textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Prompt personalizado</th>
                    <td>
                        <textarea name="custom_prompt" class="large-text code" style="min-height:80px;font-family:monospace;" rows="4" placeholder="Instrucciones adicionales para la IA (opcional)..."></textarea>
                    </td>
                </tr>
            </table>
            <div class="content-btn">
                <button type="submit" class="button button-primary">Guardar</button>
                <button type="button" class="button gpai-sitemap-generate-btn" data-sitemap-file="' . esc_attr($filename) . '">Generar con IA</button>
                <button type="submit" name="save" value="sitemap_delete" class="button" style="color:#b32d2e;" onclick="return confirm(\'¿Eliminar ' . esc_js($filename) . '?\')">Eliminar</button>
                <span class="gpai-sitemap-generate-status" style="margin-left:1rem;"></span>
            </div>
        </form>';

        echo GPAI_Collapse($collapse_title, $collapse_content, false);
        ?>
    <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.gpai-sitemap-generate-btn');
    if (!btn) return;
    const sitemapFile = btn.dataset.sitemapFile;
    const statusEl = btn.parentElement.querySelector('.gpai-sitemap-generate-status');
    const textarea = btn.closest('form').querySelector('textarea[name="sitemap_content"]');

    if (!sitemapFile) return;

    btn.disabled = true;
    if (statusEl) statusEl.textContent = 'Generando...';

    const customPrompt = btn.closest('form').querySelector('[name="custom_prompt"]') ? btn.closest('form').querySelector('[name="custom_prompt"]').value : '';

    const formData = new FormData();
    formData.append('action', 'gpai_sitemap_generate');
    formData.append('sitemap_name', sitemapFile.replace('.xml', ''));
    formData.append('custom_prompt', customPrompt);

    fetch(ajaxurl, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (textarea) textarea.value = res.data.content;
                if (statusEl) statusEl.textContent = '✓ Generado';
            } else {
                btn.disabled = false;
                if (statusEl) statusEl.textContent = '✗ ' + (res.data || 'Error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            if (statusEl) statusEl.textContent = '✗ Error de conexion';
        });
});
</script>
