<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;

$apiSeo = isset($CONFIG['api_seo']) ? $CONFIG['api_seo'] : [];
$apiSeoEnabled = !empty($apiSeo['enabled']);
$apiSeoKey = isset($apiSeo['key']) ? $apiSeo['key'] : '';
$respond_config = [];

if (isset($_POST['save']) && $_POST['save'] === 'api_seo') {
    if (!current_user_can('manage_options')) {
        wp_die('Sin permisos.');
    }
    check_admin_referer('gpai_api_seo_save', 'gpai_api_seo_nonce');

    $apiSeoEnabled = isset($_POST['api_seo_enabled']);
    $apiSeoKey = isset($_POST['api_seo_key']) ? sanitize_text_field(wp_unslash($_POST['api_seo_key'])) : '';

    $CONFIG['api_seo'] = [
        'enabled' => $apiSeoEnabled,
        'key' => $apiSeoKey,
    ];
    $GPAI_USE_DATA_CONFIG->set($CONFIG);
    $respond_config = ['status' => 'ok', 'message' => 'Configuración API SEO guardada.'];
}

$restUrl = rest_url(GPAI_KEY . '/seo');

?>
<form method="post">
    <?php FWURespond::render($respond_config) ?>
    <input type="hidden" name="save" value="api_seo">
    <?php wp_nonce_field('gpai_api_seo_save', 'gpai_api_seo_nonce'); ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API SEO", "Activa el endpoint REST para recibir datos SEO desde servicios externos.") ?>
            </th>
            <td>
                <input
                    type="checkbox"
                    id="api_seo_enabled"
                    name="api_seo_enabled"
                    <?= $apiSeoEnabled ? "checked" : "" ?>
                    class="regular-text" />
                <label for="api_seo_enabled">
                    Activar API SEO
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API Key", "Clave secreta que deben enviar los clientes en el header X-GPAI-SEO-Key.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="password"
                        id="api_seo_key"
                        name="api_seo_key"
                        value="<?= esc_attr($apiSeoKey) ?>"
                        class="regular-text"
                        placeholder="Genera una API Key..." />
                    <button type="button" class="button" id="gpai-api-seo-generate-key">Generar</button>
                    <button type="button" class="button" id="gpai-api-seo-copy-key">Copiar</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Endpoint URL", "URL del endpoint REST para enviar datos SEO.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="text"
                        id="api_seo_endpoint"
                        value="<?= esc_url($restUrl) ?>"
                        class="regular-text"
                        readonly />
                    <button type="button" class="button" id="gpai-api-seo-copy-url">Copiar URL</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Body de ejemplo", "JSON de ejemplo para enviar al endpoint. Puedes editarlo.") ?>
            </th>
            <td>
                <textarea
                    id="api_seo_example_body"
                    class="large-text code"
                    rows="12"
                    style="font-family:monospace;font-size:13px;">{
  "post_id": 1,
  "post_name": "ejemplo-slug",
  "gpai_wpseo_active": "1",
  "gpai_wpseo_title": "Título SEO de ejemplo",
  "gpai_wpseo_metadesc": "Descripción meta de ejemplo para la página.",
  "gpai_wpseo_focuskw": "palabra clave ejemplo",
  "gpai_wpseo_focuskeywords": "palabra clave 1, palabra clave 2, palabra clave 3",
  "gpai_wpseo_canonical": "https://ejemplo.com/pagina-ejemplo/",
  "gpai_wpseo_bctitle": "Título Breadcrumb",
  "gpai_wpseo_redirect": "",
  "gpai_wpseo_post_name": "ejemplo-slug",
  "gpai_wpseo_is_cornerstone": "0",
  "gpai_wpseo_meta-robots-noindex": "0",
  "gpai_wpseo_meta-robots-nofollow": "0",
  "gpai_wpseo_meta-robots-adv": "max-snippet:-1,max-image-preview:large,max-video-preview:-1",
  "gpai_wpseo_meta-robots-noarchive": "0",
  "gpai_wpseo_meta-robots-nosnippet": "0",
  "gpai_wpseo_meta-robots-noimageindex": "0",
  "gpai_wpseo_opengraph-title": "Título Open Graph de ejemplo",
  "gpai_wpseo_opengraph-description": "Descripción Open Graph de ejemplo.",
  "gpai_wpseo_opengraph-image": "https://ejemplo.com/wp-content/uploads/og-image.jpg",
  "gpai_wpseo_opengraph-image-id": "123",
  "gpai_wpseo_opengraph-url": "https://ejemplo.com/pagina-ejemplo/",
  "gpai_wpseo_twitter-title": "Título Twitter de ejemplo",
  "gpai_wpseo_twitter-description": "Descripción Twitter de ejemplo.",
  "gpai_wpseo_twitter-image": "https://ejemplo.com/wp-content/uploads/twitter-image.jpg",
  "gpai_wpseo_schema_page_type": "WebPage",
  "gpai_wpseo_schema_article_type": "Article",
  "gpai_wpseo_schema_extra_json": "[{\"@type\":\"Service\",\"name\":\"Servicio de ejemplo\"},{\"@type\":\"FAQPage\",\"mainEntity\":[{\"@type\":\"Question\",\"name\":\"¿Pregunta de ejemplo?\",\"acceptedAnswer\":{\"@type\":\"Answer\",\"text\":\"Respuesta de ejemplo.\"}}]}]",
  "gpai_wpseo_remove_other_jsonld": "0"
}</textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Página de prueba", "Selecciona una página para enviar la prueba.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <select id="api_seo_test_page" class="regular-text">
                        <option value="">— Seleccionar página —</option>
                        <?php
                        $pages = get_posts([
                            'post_type' => 'any',
                            'post_status' => 'publish',
                            'posts_per_page' => 100,
                            'orderby' => 'title',
                            'order' => 'ASC',
                        ]);
                        foreach ($pages as $p) {
                            $pt = get_post_type_object($p->post_type);
                            $label = $pt ? $pt->labels->singular_name : $p->post_type;
                            echo '<option value="' . esc_attr($p->ID) . '">' . esc_html($p->post_title) . ' (ID: ' . $p->ID . ', ' . $label . ')</option>';
                        }
                        ?>
                    </select>
                    <button type="button" class="button button-primary" id="gpai-api-seo-test-btn">Ejecutar Prueba</button>
                </div>
                <div id="gpai-api-seo-test-result" style="margin-top:10px;"></div>
            </td>
        </tr>
    </table>

    <div class="content-btn">
        <button
            type="submit"
            name="submit"
            value="Guardar"
            class="button button-primary">
            Guardar
        </button>
    </div>
</form>

<script>
jQuery(function($) {
    var generateBtn = document.getElementById('gpai-api-seo-generate-key');
    var copyKeyBtn = document.getElementById('gpai-api-seo-copy-key');
    var copyUrlBtn = document.getElementById('gpai-api-seo-copy-url');
    var keyInput = document.getElementById('api_seo_key');
    var testBtn = document.getElementById('gpai-api-seo-test-btn');
    var testResult = document.getElementById('gpai-api-seo-test-result');
    var pageSelect = document.getElementById('api_seo_test_page');
    var bodyTextarea = document.getElementById('api_seo_example_body');

    function generateApiKey() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = 'gpai_';
        var array = new Uint8Array(48);
        crypto.getRandomValues(array);
        for (var i = 0; i < 48; i++) {
            result += chars[array[i] % chars.length];
        }
        return result;
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            keyInput.value = generateApiKey();
        });
    }

    if (copyKeyBtn) {
        copyKeyBtn.addEventListener('click', function() {
            keyInput.type = 'text';
            keyInput.select();
            document.execCommand('copy');
            keyInput.type = 'password';
            copyKeyBtn.textContent = '✓ Copiado';
            setTimeout(function() { copyKeyBtn.textContent = 'Copiar'; }, 2000);
        });
    }

    if (copyUrlBtn) {
        copyUrlBtn.addEventListener('click', function() {
            var urlInput = document.getElementById('api_seo_endpoint');
            urlInput.select();
            document.execCommand('copy');
            copyUrlBtn.textContent = '✓ Copiado';
            setTimeout(function() { copyUrlBtn.textContent = 'Copiar URL'; }, 2000);
        });
    }

    if (testBtn) {
        testBtn.addEventListener('click', function() {
            var pageId = pageSelect.value;
            if (!pageId) {
                testResult.innerHTML = '<div class="notice notice-error inline"><p>Selecciona una página primero.</p></div>';
                return;
            }

            var apiKey = keyInput.value;
            if (!apiKey) {
                testResult.innerHTML = '<div class="notice notice-error inline"><p>Genera o ingresa una API Key primero.</p></div>';
                return;
            }

            var bodyText = bodyTextarea.value;
            var bodyJson;
            try {
                bodyJson = JSON.parse(bodyText);
            } catch (e) {
                testResult.innerHTML = '<div class="notice notice-error inline"><p>El JSON del body de ejemplo no es válido: ' + e.message + '</p></div>';
                return;
            }

            bodyJson.post_id = parseInt(pageId, 10);

            testBtn.disabled = true;
            testBtn.textContent = 'Enviando...';
            testResult.innerHTML = '<div class="notice notice-info inline"><p>Enviando petición...</p></div>';

            var endpoint = document.getElementById('api_seo_endpoint').value;

            $.ajax({
                url: endpoint,
                method: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-GPAI-SEO-Key': apiKey,
                },
                data: JSON.stringify(bodyJson),
                success: function(res) {
                    if (res.success) {
                        testResult.innerHTML = '<div class="notice notice-success inline"><p>✓ ' + (res.message || 'Datos guardados.') + '</p><pre style="background:#f0f0f1;padding:8px;margin-top:8px;max-height:200px;overflow:auto;">' + JSON.stringify(res.data, null, 2) + '</pre></div>';
                    } else {
                        testResult.innerHTML = '<div class="notice notice-error inline"><p>✗ ' + (res.message || 'Error') + '</p></div>';
                    }
                    testBtn.disabled = false;
                    testBtn.textContent = 'Ejecutar Prueba';
                },
                error: function(jqXHR) {
                    var msg = 'Error de conexión';
                    try {
                        var r = JSON.parse(jqXHR.responseText);
                        msg = r.message || msg;
                    } catch(e) {}
                    testResult.innerHTML = '<div class="notice notice-error inline"><p>✗ ' + msg + '</p></div>';
                    testBtn.disabled = false;
                    testBtn.textContent = 'Ejecutar Prueba';
                }
            });
        });
    }
});
</script>
<?php
