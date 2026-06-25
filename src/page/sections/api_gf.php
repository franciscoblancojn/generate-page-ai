<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;

$apiGf = isset($CONFIG['api_gf']) ? $CONFIG['api_gf'] : [];
$apiGfEnabled = !empty($apiGf['enabled']);
$apiGfKey = isset($apiGf['key']) ? $apiGf['key'] : '';
$respond_config = [];

if (isset($_POST['save']) && $_POST['save'] === 'api_gf') {
    if (!current_user_can('manage_options')) {
        wp_die('Sin permisos.');
    }
    check_admin_referer('gpai_api_gf_save', 'gpai_api_gf_nonce');

    $apiGfEnabled = isset($_POST['api_gf_enabled']);
    $apiGfKey = isset($_POST['api_gf_key']) ? sanitize_text_field(wp_unslash($_POST['api_gf_key'])) : '';

    $CONFIG['api_gf'] = [
        'enabled' => $apiGfEnabled,
        'key' => $apiGfKey,
    ];
    $GPAI_USE_DATA_CONFIG->set($CONFIG);
    $respond_config = ['status' => 'ok', 'message' => 'Configuración API Global Fields guardada.'];
}

$getUrl = rest_url(GPAI_KEY . '/gf/get');
$setUrl = rest_url(GPAI_KEY . '/gf/set');

?>
<form method="post">
    <?php FWURespond::render($respond_config) ?>
    <input type="hidden" name="save" value="api_gf">
    <?php wp_nonce_field('gpai_api_gf_save', 'gpai_api_gf_nonce'); ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API Global Fields", "Activa los endpoints REST para obtener y modificar campos globales.") ?>
            </th>
            <td>
                <input
                    type="checkbox"
                    id="api_gf_enabled"
                    name="api_gf_enabled"
                    <?= esc_attr($apiGfEnabled ? 'checked' : '') ?>
                    class="regular-text" />
                <label for="api_gf_enabled">
                    Activar API Global Fields
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API Key", "Clave secreta en header X-GPAI-GF-Key.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="password"
                        id="api_gf_key"
                        name="api_gf_key"
                        value="<?= esc_attr($apiGfKey) ?>"
                        class="regular-text"
                        placeholder="Genera una API Key..." />
                    <button type="button" class="button" id="gpai-api-gf-generate-key">Generar</button>
                    <button type="button" class="button" id="gpai-api-gf-copy-key">Copiar</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("GET Endpoint", "Obtiene todos los campos globales.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="text"
                        id="api_gf_get_endpoint"
                        value="<?= esc_url($getUrl) ?>"
                        class="regular-text"
                        readonly />
                    <button type="button" class="button" id="gpai-api-gf-copy-get-url">Copiar GET URL</button>
                </div>
                <button type="button" class="button button-primary" id="gpai-api-gf-get-test-btn" style="margin-top:8px;">Probar GET</button>
                <div id="gpai-api-gf-get-result" style="margin-top:8px;"></div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("POST Endpoint", "Crea o modifica campos globales. Envía JSON directo con clave: valor.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="text"
                        id="api_gf_set_endpoint"
                        value="<?= esc_url($setUrl) ?>"
                        class="regular-text"
                        readonly />
                    <button type="button" class="button" id="gpai-api-gf-copy-set-url">Copiar POST URL</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Body de ejemplo (POST)", "JSON directo con pares clave: valor. Los valores pueden contener HTML.") ?>
            </th>
            <td>
                <textarea
                    id="api_gf_example_body"
                    class="large-text code"
                    rows="8"
                    style="font-family:monospace;font-size:13px;">{
  "telefono_contacto": "+57 601 123 4567",
  "email_contacto": "info@ejemplo.com",
  "direccion_oficina": "Calle 123 #45-67, Bogotá<br>Colombia",
  "horario_atencion": "Lun-Vie 8am a 6pm",
  "texto_banner_principal": "Bienvenido a nuestro sitio web"
}</textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Probar POST", "Envía el body de ejemplo para guardar los campos globales.") ?>
            </th>
            <td>
                <button type="button" class="button button-primary" id="gpai-api-gf-set-test-btn">Probar POST</button>
                <div id="gpai-api-gf-set-result" style="margin-top:8px;"></div>
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
    var keyInput = document.getElementById('api_gf_key');

    function generateApiKey() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = 'gf_';
        var array = new Uint8Array(48);
        crypto.getRandomValues(array);
        for (var i = 0; i < 48; i++) {
            result += chars[array[i] % chars.length];
        }
        return result;
    }

    document.getElementById('gpai-api-gf-generate-key').addEventListener('click', function() {
        keyInput.value = generateApiKey();
    });

    document.getElementById('gpai-api-gf-copy-key').addEventListener('click', function() {
        keyInput.type = 'text';
        keyInput.select();
        document.execCommand('copy');
        keyInput.type = 'password';
        this.textContent = '✓ Copiado';
        setTimeout(function() { this.textContent = 'Copiar'; }.bind(this), 2000);
    });

    document.getElementById('gpai-api-gf-copy-get-url').addEventListener('click', function() {
        var el = document.getElementById('api_gf_get_endpoint');
        el.select();
        document.execCommand('copy');
        this.textContent = '✓ Copiado';
        setTimeout(function() { this.textContent = 'Copiar GET URL'; }.bind(this), 2000);
    });

    document.getElementById('gpai-api-gf-copy-set-url').addEventListener('click', function() {
        var el = document.getElementById('api_gf_set_endpoint');
        el.select();
        document.execCommand('copy');
        this.textContent = '✓ Copiado';
        setTimeout(function() { this.textContent = 'Copiar POST URL'; }.bind(this), 2000);
    });

    // GET test
    document.getElementById('gpai-api-gf-get-test-btn').addEventListener('click', function() {
        var resultEl = document.getElementById('gpai-api-gf-get-result');
        var apiKey = keyInput.value;

        if (!apiKey) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>Genera o ingresa una API Key primero.</p></div>';
            return;
        }

        this.disabled = true;
        this.textContent = 'Obteniendo...';
        resultEl.innerHTML = '<div class="notice notice-info inline"><p>Solicitando campos globales...</p></div>';

        $.ajax({
            url: document.getElementById('api_gf_get_endpoint').value,
            method: 'GET',
            headers: { 'X-GPAI-GF-Key': apiKey },
            success: function(res) {
                if (res.success) {
                    var jsonStr = JSON.stringify(res.data.fields, null, 2).replace(/\\\//g, '/').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    resultEl.innerHTML = '<div class="notice notice-success inline"><p>✓ ' + Object.keys(res.data.fields).length + ' campos globales.</p><pre style="background:#f0f0f1;padding:8px;margin-top:8px;max-height:300px;overflow:auto;">' + jsonStr + '</pre></div>';
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
    document.getElementById('gpai-api-gf-set-test-btn').addEventListener('click', function() {
        var resultEl = document.getElementById('gpai-api-gf-set-result');
        var apiKey = keyInput.value;

        if (!apiKey) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>Genera o ingresa una API Key primero.</p></div>';
            return;
        }

        var bodyText = document.getElementById('api_gf_example_body').value;
        var bodyJson;
        try {
            bodyJson = JSON.parse(bodyText);
        } catch (e) {
            resultEl.innerHTML = '<div class="notice notice-error inline"><p>El JSON no es válido: ' + e.message + '</p></div>';
            return;
        }

        this.disabled = true;
        this.textContent = 'Enviando...';
        resultEl.innerHTML = '<div class="notice notice-info inline"><p>Enviando petición...</p></div>';

        $.ajax({
            url: document.getElementById('api_gf_set_endpoint').value,
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-GPAI-GF-Key': apiKey },
            data: JSON.stringify(bodyJson),
            success: function(res) {
                if (res.success) {
                    var jsonStr = JSON.stringify(res.data, null, 2).replace(/\\\//g, '/').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    resultEl.innerHTML = '<div class="notice notice-success inline"><p>✓ ' + (res.message || 'Guardado.') + '</p><pre style="background:#f0f0f1;padding:8px;margin-top:8px;max-height:200px;overflow:auto;">' + jsonStr + '</pre></div>';
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
