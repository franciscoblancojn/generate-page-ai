<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$respond_crear = null;

if (isset($_POST['save']) && $_POST['save'] == "crear_sitemap") {
    $name = isset($_POST['sitemap_name'])
        ? trim($_POST['sitemap_name'])
        : '';
    $content = isset($_POST['sitemap_content'])
        ? $_POST['sitemap_content']
        : '';

    if (!str_ends_with($name, '.xml')) {
        $name .= '.xml';
    }

    if (!empty($name)) {
        $created = $GPAI_USE_DATA_SITEMAPS->createSitemap($name, $content);
        if ($created) {
            $respond_crear = [
                "status" => "ok",
                "message" => "Site Map creado: " . esc_html($name),
                'data' => [
                    'url' => home_url($name),
                ],
            ];
            $_POST = [];
        } else {
            $respond_crear = [
                "status" => "error",
                "message" => "El archivo ya existe o no se pudo crear.",
                'data' => [],
            ];
        }
    } else {
        $respond_crear = [
            "status" => "error",
            "message" => "El nombre del Site Map es requerido.",
            'data' => [],
        ];
    }

    FWUSystemLog::add(GPAI_KEY, [
        'type' => "crear_sitemap",
        'data' => $_POST
    ]);
}

?>
<?= GPAI_Respond($respond_crear) ?>

<form method="post">
    <input type="hidden" name="save" value="crear_sitemap">
    <table class="form-table">
        <tr>
            <th scope="row">
                <?= GPAI_Tooltip("Nombre", "Nombre del archivo XML. Ej: sitemap-posts, sitemap-pages. Se agregara .xml automaticamente.") ?>
            </th>
            <td>
                <input
                    type="text"
                    name="sitemap_name"
                    value="<?= isset($_POST['sitemap_name']) ? esc_attr($_POST['sitemap_name']) : '' ?>"
                    class="regular-text"
                    style="width:100%;"
                    placeholder="Ej: sitemap-posts"
                    required>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?= GPAI_Tooltip("Contenido XML", "Contenido XML del Site Map. Deja vacio para llenarlo despues con generar.") ?>
            </th>
            <td>
                <textarea
                    name="sitemap_content"
                    class="large-text code"
                    style="min-height:300px;font-family:monospace;"
                    rows="15"
                    placeholder="Pega o genera el contenido XML del sitemap..."><?= isset($_POST['sitemap_content']) ? esc_textarea($_POST['sitemap_content']) : '' ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?= GPAI_Tooltip("Prompt personalizado", "Instrucciones adicionales para la IA al generar el contenido XML.") ?>
            </th>
            <td>
                <textarea
                    name="custom_prompt"
                    class="large-text code"
                    style="min-height:80px;font-family:monospace;"
                    rows="4"
                    placeholder="Ej: Incluye solo URLs de categorias, con prioridad 0.5..."></textarea>
            </td>
        </tr>
    </table>
    <div class="content-btn">
        <button type="submit" class="button button-primary">Crear Site Map</button>
        <button type="button" class="button gpai-sitemap-crear-generate-btn">Generar con IA</button>
        <span class="gpai-sitemap-crear-generate-status" style="margin-left:1rem;"></span>
    </div>
</form>

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.gpai-sitemap-crear-generate-btn');
    if (!btn) return;
    const nameInput = btn.closest('form').querySelector('[name="sitemap_name"]');
    const contentTextarea = btn.closest('form').querySelector('[name="sitemap_content"]');
    const statusEl = btn.parentElement.querySelector('.gpai-sitemap-crear-generate-status');

    const sitemapName = nameInput ? nameInput.value.trim() : '';
    if (!sitemapName) {
        if (statusEl) statusEl.textContent = '✗ Primero escribe un nombre';
        return;
    }

    const customPrompt = btn.closest('form').querySelector('[name="custom_prompt"]') ? btn.closest('form').querySelector('[name="custom_prompt"]').value : '';

    btn.disabled = true;
    if (statusEl) statusEl.textContent = 'Generando...';

    const formData = new FormData();
    formData.append('action', 'gpai_sitemap_generate');
    formData.append('sitemap_name', sitemapName.replace(/\.xml$/i, ''));
    formData.append('custom_prompt', customPrompt);

    fetch(ajaxurl, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (contentTextarea) contentTextarea.value = res.data.content;
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
