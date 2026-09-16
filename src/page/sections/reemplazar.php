<?php

use franciscoblancojn\wordpress_utils\FWUTooltip;

$htaccessWritable = !empty($HTACCESS['writable']);

?>
<style>
    #gpai-reemplazar-result .gpai-msg {
        font-weight: 900;
        padding: 0.75rem 1rem;
        border-radius: 0.4rem;
        margin: 0.5rem 0;
    }
    #gpai-reemplazar-result .gpai-loading {
        color: #1d2327;
        background: #f0f6fc;
    }
    #gpai-reemplazar-result .gpai-ok {
        color: #fff;
        background: #25992f;
    }
    #gpai-reemplazar-result .gpai-error {
        color: #fff;
        background: #d63638;
    }
    #gpai-reemplazar-result .gpai-info {
        color: #1d2327;
        background: #f0f6fc;
    }
    #gpai-reemplazar-result .gpai-result-box {
        background: #fff;
        border: 1px solid #dcdcde;
        border-radius: 0.4rem;
        padding: 0.75rem 1rem;
        margin: 0.75rem 0;
    }
    #gpai-reemplazar-result .gpai-result-box h3 {
        margin: 0 0 0.5rem;
        font-size: 0.95rem;
    }
    #gpai-reemplazar-result .gpai-result-box ul {
        margin: 0.25rem 0;
        padding-left: 1.25rem;
        font-size: 13px;
        line-height: 1.6;
    }
    #gpai-reemplazar-result .gpai-result-box pre {
        margin: 0;
        background: #1d2327;
        color: #00ff88;
        padding: 0.75rem;
        border-radius: 0.35rem;
        white-space: pre-wrap;
        overflow: auto;
        font-size: 12px;
    }
    #gpai-reemplazar-result .gpai-note {
        font-size: 12px;
        margin: 0.25rem 0;
    }
</style>

<div class="gpai-section">
    <form method="post" id="gpai-reemplazar-form" data-nonce="<?= esc_attr(wp_create_nonce('gpai_nonce')) ?>" data-api-key="<?= esc_attr(GPAI_API_KEY_INTERNA) ?>">
        <input type="hidden" name="save" value="gpai_reemplazar_url">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php FWUTooltip::render("URL Vieja", "URL o fragmento a reemplazar. Ej: my-old-url/sub-url/") ?>
                </th>
                <td>
                    <input
                        type="text"
                        name="url_vieja"
                        id="gpai-reemplazar-url-vieja"
                        class="regular-text code"
                        placeholder="ej: my-old-url/sub-url/"
                        style="width:100%"
                        required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php FWUTooltip::render("URL Nueva", "URL o fragmento que reemplazará a la URL vieja. Ej: my-url/sub-url/") ?>
                </th>
                <td>
                    <input
                        type="text"
                        name="url_nueva"
                        id="gpai-reemplazar-url-nueva"
                        class="regular-text code"
                        placeholder="ej: my-url/sub-url/"
                        style="width:100%"
                        required>
                </td>
            </tr>
            <tr>
                <th scope="row">Redirección</th>
                <td>
                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                        <label class="gpai-checkbox-label" style="display:inline-flex;gap:.4rem;align-items:center;">
                            <input
                                type="checkbox"
                                name="agregar_redirect"
                                id="gpai-reemplazar-redirect"
                                value="1"
                                <?= $htaccessWritable ? 'checked' : 'disabled' ?>>
                            <span>Agregar redirección por defecto</span>
                        </label>
                        <?php if (!$htaccessWritable): ?>
                            <span class="gpai-badge" style="color:#d63638;">Sin permisos de escritura en .htaccess</span>
                            <?php FWUTooltip::render(
                                "Redirección desactivada",
                                "El archivo .htaccess no tiene permisos de escritura, por lo que no se agregará la regla de redirección. El reemplazo en la base de datos aún se ejecutará. Da permisos de escritura al archivo .htaccess para habilitar esta opción."
                            ) ?>
                        <?php else: ?>
                            <?php FWUTooltip::render(
                                "Redirección 301",
                                "Al activar esta opción se agrega una regla de redirección 301 en el .htaccess: RewriteRule ^url_vieja/?$ /url_nueva [R=301,L]"
                            ) ?>
                        <?php endif; ?>
                    </div>
                    <p class="description">
                        Reemplaza la URL o fragmento en toda la base de datos (equivalente a
                        <code>wp search-replace 'url_vieja' 'url_nueva' --all-tables</code>).
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">.htaccess</th>
                <td>
                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                        <code><?= esc_html($HTACCESS['path']) ?></code>
                        <?php if ($HTACCESS['exists']): ?>
                            <span class="gpai-badge gpai-badge-ok" style="color:#00a32a;">Existe</span>
                            <span style="font-size:12px;color:#666;">
                                <?= size_format($HTACCESS['size']) ?> — <?= date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $HTACCESS['modified']) ?>
                            </span>
                        <?php else: ?>
                            <span class="gpai-badge" style="color:#d63638;">No existe</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
        <div class="content-btn">
            <button type="submit" id="gpai-reemplazar-submit" class="button button-primary">Reemplazar</button>
        </div>
    </form>
</div>

<div id="gpai-reemplazar-result"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('gpai-reemplazar-form');
    var resultEl = document.getElementById('gpai-reemplazar-result');
    var btn = document.getElementById('gpai-reemplazar-submit');
    if (!form || !resultEl || !btn) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (btn.disabled) return;
        btn.disabled = true;
        btn.textContent = 'Reemplazando...';
        resultEl.innerHTML = '<p class="gpai-msg gpai-loading">Ejecutando reemplazo en la base de datos...</p>';

        var formData = new FormData(form);
        formData.append('action', 'gpai_reemplazar_url');
        formData.append('nonce', form.dataset.nonce);
        formData.append('api_key', form.dataset.apiKey);

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.classList.remove('fwue-loader');
                btn.textContent = 'Reemplazar';
                renderResult(res);
            })
            .catch(function() {
                btn.disabled = false;
                btn.classList.remove('fwue-loader');
                btn.textContent = 'Reemplazar';
                resultEl.innerHTML = '<p class="gpai-msg gpai-error">Error de conexión. Intenta de nuevo.</p>';
            });
    });

    function renderResult(res) {
        var isOk = !!res.success;
        var data = res.data || {};
        var html = '<p class="gpai-msg ' + (isOk ? 'gpai-ok' : 'gpai-error') + '">'
            + escHtml(data.message || (isOk ? 'Completado.' : 'Error.'))
            + '</p>';

        var report = data.report;
        if (report && typeof report === 'object') {
            html += '<div class="gpai-result-box"><h3>Resultado search-replace (BD)</h3><ul>'
                + '<li>Tablas procesadas: ' + escHtml(report.tables) + '</li>'
                + '<li>Tablas con cambios: ' + escHtml(report.tables_with_changes) + '</li>'
                + '<li>Filas actualizadas: ' + escHtml(report.rows_updated) + '</li>'
                + '<li>Celdas actualizadas: ' + escHtml(report.cells_updated) + '</li>'
                + '<li>Tiempo: ' + escHtml(report.elapsed) + ' s</li>'
                + '</ul></div>';

            if (report.tables_skipped && report.tables_skipped.length) {
                html += '<p class="gpai-note">Tablas omitidas: ' + escHtml(report.tables_skipped.join(', ')) + '</p>';
            }
            if (report.errors && report.errors.length) {
                html += '<p class="gpai-note" style="color:#d63638;">Errores: ' + escHtml(report.errors.slice(0, 5).join(' | ')) + '</p>';
            }
        }

        if (data.rule) {
            html += '<div class="gpai-result-box"><h3>Regla agregada al .htaccess</h3><pre>'
                + escHtml(data.rule) + '</pre></div>';
        }

        var redirect = data.redirect;
        if (redirect && typeof redirect === 'object') {
            var cls = redirect.status === 'ok' ? 'gpai-ok' : (redirect.status === 'skipped' ? 'gpai-info' : 'gpai-error');
            html += '<div class="gpai-result-box"><h3>Redirección .htaccess</h3><p class="gpai-msg ' + cls + '">'
                + escHtml(redirect.message) + '</p></div>';
        }

        resultEl.innerHTML = html;
    }

    function escHtml(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
});
</script>