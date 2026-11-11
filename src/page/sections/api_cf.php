<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;

$apiCf = isset($CONFIG['api_cf']) ? $CONFIG['api_cf'] : [];
$apiCfEnabled = !empty($apiCf['enabled']);
$apiCfKey = isset($apiCf['key']) ? $apiCf['key'] : '';
$respond_config = [];

if (isset($_POST['save']) && $_POST['save'] === 'api_cf') {
    if (!current_user_can('manage_options')) {
        wp_die('Sin permisos.');
    }
    check_admin_referer('gpai_api_cf_save', 'gpai_api_cf_nonce');

    $apiCfEnabled = isset($_POST['api_cf_enabled']);
    $apiCfKey = isset($_POST['api_cf_key']) ? sanitize_text_field(wp_unslash($_POST['api_cf_key'])) : '';

    $CONFIG['api_cf'] = [
        'enabled' => $apiCfEnabled,
        'key' => $apiCfKey,
    ];
    $GPAI_USE_DATA_CONFIG->set($CONFIG);
    $respond_config = ['status' => 'ok', 'message' => 'Configuración API Custom Fields guardada.'];
}

$getUrl = rest_url(GPAI_KEY . '/cf/get/');
$setUrl = rest_url(GPAI_KEY . '/cf/set/');

?>
<form method="post">
    <?php FWURespond::render($respond_config) ?>
    <input type="hidden" name="save" value="api_cf">
    <?php wp_nonce_field('gpai_api_cf_save', 'gpai_api_cf_nonce'); ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API Custom Fields", "Activa los endpoints REST para obtener y modificar campos personalizados desde servicios externos.") ?>
            </th>
            <td>
                <input
                    type="checkbox"
                    id="api_cf_enabled"
                    name="api_cf_enabled"
                    <?= esc_attr($apiCfEnabled ? 'checked' : '') ?>
                    class="regular-text" />
                <label for="api_cf_enabled">
                    Activar API Custom Fields
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API Key", "Clave secreta que deben enviar los clientes en el header X-GPAI-CF-Key.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="password"
                        id="api_cf_key"
                        name="api_cf_key"
                        value="<?= esc_attr($apiCfKey) ?>"
                        class="regular-text"
                        placeholder="Genera una API Key..." />
                    <button type="button" class="button" id="gpai-api-cf-generate-key">Generar</button>
                    <button type="button" class="button" id="gpai-api-cf-copy-key">Copiar</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("GET Endpoint", "Obtiene todos los custom fields de una página. Envía API Key en header X-GPAI-CF-Key.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="text"
                        id="api_cf_get_endpoint"
                        value="<?= esc_url($getUrl) ?>"
                        class="regular-text"
                        readonly />
                    <button type="button" class="button" id="gpai-api-cf-copy-get-url">Copiar GET URL</button>
                </div>
                <p class="description">Ejemplo: <code>GET <?= esc_url($getUrl) ?>?post_id=1</code></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Probar GET", "Selecciona una página y obtén sus campos personalizados.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <select id="api_cf_get_page" class="regular-text">
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
                    <button type="button" class="button button-primary" id="gpai-api-cf-get-test-btn">Probar GET</button>
                </div>
                <div id="gpai-api-cf-get-result" style="margin-top:10px;"></div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("POST Endpoint", "Modifica los valores de campos personalizados. Envía API Key en header X-GPAI-CF-Key.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="text"
                        id="api_cf_set_endpoint"
                        value="<?= esc_url($setUrl) ?>"
                        class="regular-text"
                        readonly />
                    <button type="button" class="button" id="gpai-api-cf-copy-set-url">Copiar POST URL</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Body de ejemplo (POST)", "JSON de ejemplo para crear o modificar campos personalizados. Puedes editarlo.") ?>
            </th>
            <td>
                <textarea
                    id="api_cf_example_body"
                    class="large-text code"
                    rows="10"
                    style="font-family:monospace;font-size:13px;">{
  "post_id": 1,
  "mi_campo_personalizado": "Valor de ejemplo",
  "otro_campo": "Otro valor",
  "campo_numerico": "123"
}</textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Probar POST", "Selecciona una página y envía el body de ejemplo para guardar los campos.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <select id="api_cf_set_page" class="regular-text">
                        <option value="">— Seleccionar página —</option>
                        <?php
                        foreach ($pages as $p) {
                            $pt = get_post_type_object($p->post_type);
                            $label = $pt ? $pt->labels->singular_name : $p->post_type;
                            echo '<option value="' . esc_attr($p->ID) . '">' . esc_html($p->post_title) . ' (ID: ' . $p->ID . ', ' . $label . ')</option>';
                        }
                        ?>
                    </select>
                    <button type="button" class="button button-primary" id="gpai-api-cf-set-test-btn">Probar POST</button>
                </div>
                <div id="gpai-api-cf-set-result" style="margin-top:10px;"></div>
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
    var keyInput = document.getElementById('api_cf_key');

    function generateApiKey() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = 'cf_';
        var array = new Uint8Array(48);
        crypto.getRandomValues(array);
        for (var i = 0; i < 48; i++) {
            result += chars[array[i] % chars.length];
        }
        return result;
    }

    document.getElementById('gpai-api-cf-generate-key').addEventListener('click', function() {
        keyInput.value = generateApiKey();
    });

    document.getElementById('gpai-api-cf-copy-key').addEventListener('click', function() {
        keyInput.type = 'text';
        keyInput.select();
        document.execCommand('copy');
        keyInput.type = 'password';
        this.textContent = '✓ Copiado';
        setTimeout(function() { this.textContent = 'Copiar'; }.bind(this), 2000);
    });

    document.getElementById('gpai-api-cf-copy-get-url').addEventListener('click', function() {
        var el = document.getElementById('api_cf_get_endpoint');
        el.select();
        document.execCommand('copy');
        this.textContent = '✓ Copiado';
        setTimeout(function() { this.textContent = 'Copiar GET URL'; }.bind(this), 2000);
    });

    document.getElementById('gpai-api-cf-copy-set-url').addEventListener('click', function() {
        var el = document.getElementById('api_cf_set_endpoint');
        el.select();
        document.execCommand('copy');
        this.textContent = '✓ Copiado';
        setTimeout(function() { this.textContent = 'Copiar POST URL'; }.bind(this), 2000);
    });

    // GET test
    document.getElementById('gpai-api-cf-get-test-btn').addEventListener('click', function() {
        var pageId = document.getElementById('api_cf_get_page').value;
        var resultEl = document.getElementById('gpai-api-cf-get-result');
        var apiKey = keyInput.value;

        if (!pageId) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>Selecciona una página primero.</p></div>';
            return;
        }
        if (!apiKey) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>Genera o ingresa una API Key primero.</p></div>';
            return;
        }

        this.disabled = true;
        this.textContent = 'Obteniendo...';
        resultEl.innerHTML = '<div class="notice notice-info inline"><p>Solicitando campos personalizados...</p></div>';

        var endpoint = document.getElementById('api_cf_get_endpoint').value;

        $.ajax({
            url: endpoint,
            method: 'GET',
            data: { post_id: pageId },
            headers: { 'X-GPAI-CF-Key': apiKey },
            success: function(res) {
                if (res.success) {
                    var jsonStr = JSON.stringify(res.data.fields, null, 2);
                    jsonStr = jsonStr.replace(/\\\//g, '/').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    var html = '<div class="notice notice-success inline"><p>✓ ' + Object.keys(res.data.fields).length + ' campos encontrados.</p><pre style="background:#f0f0f1;padding:8px;margin-top:8px;max-height:300px;overflow:auto;">' + jsonStr + '</pre></div>';
                    resultEl.innerHTML = html;
                } else {
                    resultEl.innerHTML = '<div class="notice notice-error inline"><p>✗ ' + (res.message || 'Error') + '</p></div>';
                }
                this.disabled = false;
                this.textContent = 'Probar GET';
            }.bind(this),
            error: function(jqXHR) {
                var msg = 'Error de conexión';
                try { var r = JSON.parse(jqXHR.responseText); msg = r.message || msg; } catch(e) {}
                resultEl.innerHTML = '<div class="notice notice-error inline"><p>✗ ' + msg + '</p></div>';
                this.disabled = false;
                this.textContent = 'Probar GET';
            }.bind(this)
        });
    });

    // POST test
    document.getElementById('gpai-api-cf-set-test-btn').addEventListener('click', function() {
        var pageId = document.getElementById('api_cf_set_page').value;
        var resultEl = document.getElementById('gpai-api-cf-set-result');
        var apiKey = keyInput.value;

        if (!pageId) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>Selecciona una página primero.</p></div>';
            return;
        }
        if (!apiKey) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>Genera o ingresa una API Key primero.</p></div>';
            return;
        }

        var bodyText = document.getElementById('api_cf_example_body').value;
        var bodyJson;
        try {
            bodyJson = JSON.parse(bodyText);
        } catch (e) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>El JSON del body de ejemplo no es válido: ' + e.message + '</p></div>';
            return;
        }

        bodyJson.post_id = parseInt(pageId, 10);

        this.disabled = true;
        this.textContent = 'Enviando...';
        resultEl.innerHTML = '<div class="notice notice-info inline"><p>Enviando petición...</p></div>';

        var endpoint = document.getElementById('api_cf_set_endpoint').value;

        $.ajax({
            url: endpoint,
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-GPAI-CF-Key': apiKey },
            data: JSON.stringify(bodyJson),
            success: function(res) {
                if (res.success) {
                    var jsonStr = JSON.stringify(res.data, null, 2).replace(/\\\//g, '/').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    resultEl.innerHTML = '<div class="notice notice-success inline"><p>✓ ' + (res.message || 'Campos guardados.') + '</p><pre style="background:#f0f0f1;padding:8px;margin-top:8px;max-height:200px;overflow:auto;">' + jsonStr + '</pre></div>';
                } else {
                    resultEl.innerHTML = '<div class="notice notice-error inline"><p>✗ ' + (res.message || 'Error') + '</p></div>';
                }
                this.disabled = false;
                this.textContent = 'Probar POST';
            }.bind(this),
            error: function(jqXHR) {
                var msg = 'Error de conexión';
                try { var r = JSON.parse(jqXHR.responseText); msg = r.message || msg; } catch(e) {}
                resultEl.innerHTML = '<div class="notice notice-error inline"><p>✗ ' + msg + '</p></div>';
                this.disabled = false;
                this.textContent = 'Probar POST';
            }.bind(this)
        });
    });
});
</script>
<?php
