<?php

use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUSystemLog;

$GPAI_USE_DATA_HTACCESS = new GPAI_USE_DATA_HTACCESS();
$HTACCESS = $GPAI_USE_DATA_HTACCESS->get();

$respond_redirects = null;
$REDIRECTS = GPAI_REEMPLAZAR::listRedirectsFromContent($HTACCESS['content']);

if (isset($_POST['save']) && $_POST['save'] === 'gpai_redirects_update') {
    check_admin_referer('gpai_redirects_save');

    if (!current_user_can('manage_options')) {
        $respond_redirects = [
            'status' => 'error',
            'message' => 'No tienes permisos para realizar esta acción.',
            'data' => [],
        ];
    } elseif (!$HTACCESS['writable']) {
        $respond_redirects = [
            'status' => 'error',
            'message' => 'El archivo .htaccess no tiene permisos de escritura.',
            'data' => [],
        ];
    } else {
        $redirects = [];
        $postRedirects = isset($_POST['redirects']) && is_array($_POST['redirects']) ? $_POST['redirects'] : [];

        foreach ($postRedirects as $item) {
            $old = isset($item['old']) ? GPAI_REEMPLAZAR::sanitizeUrlField($item['old']) : '';
            $new = isset($item['new']) ? GPAI_REEMPLAZAR::sanitizeUrlField($item['new']) : '';

            if ($old === '' || $new === '' || $old === $new) {
                continue;
            }

            $redirects[] = [
                'old' => $old,
                'new' => $new,
            ];
        }

        if (!$HTACCESS['exists']) {
            $GPAI_USE_DATA_HTACCESS->backup();
        }

        $result = GPAI_REEMPLAZAR::saveRedirectsToHtaccess($redirects);

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'save_redirects_ui',
            'status' => $result['status'],
            'redirects' => $redirects,
        ]);

        $respond_redirects = [
            'status' => $result['status'],
            'message' => $result['message'],
            'data' => [],
        ];

        $HTACCESS = $GPAI_USE_DATA_HTACCESS->get();
        $REDIRECTS = GPAI_REEMPLAZAR::listRedirectsFromContent($HTACCESS['content']);
    }
}

$htaccessWritable = !empty($HTACCESS['writable']);

?>
<style>
    #gpai-redirect-rows input[type="text"] {
        width: 100%;
        font-family: Consolas, Monaco, monospace;
    }
    .gpai-redirect-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 0.5rem;
    }
    .gpai-redirect-actions .submit {
        margin: 0;
        padding: 0;
    }
    .gpai-redirect-msg {
        color: #d63638;
        font-weight: 600;
        padding: 0.5rem 0;
    }
    .gpai-redirect-ok {
        color: #25992f;
        font-weight: 600;
        padding: 0.5rem 0;
    }
    .gpai-redirect-empty {
        padding: 1rem;
        background: #f6f7f7;
        border: 1px solid #dcdcde;
        border-radius: 0.4rem;
        color: #50575e;
    }
</style>

<?php FWURespond::render($respond_redirects) ?>

<div class="gpai-section">
    <h2>Redirects de .htaccess</h2>
    <p class="description">
        Analiza las redirecciones 301 gestionadas por este plugin (bloque
        <code># GPAI Redirect URL</code>). Edita las URLs y pulsa
        <strong>Guardar redirects</strong> para actualizar el archivo .htaccess.
        Los cambios se aplican a .htaccess únicamente al guardar.
    </p>

    <?php if (!$htaccessWritable): ?>
        <div class="notice notice-warning inline" style="margin:0.75rem 0;">
            <p><strong>Importante:</strong> El archivo .htaccess no tiene permisos de escritura en el servidor. Edita, crea o elimina tus redirects y pulsa <strong>Descargar .htaccess</strong> para obtener el archivo actualizado. Luego sube ese archivo manualmente al servidor para que los redirects funcionen.</p>
        </div>
    <?php endif; ?>

    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;">
        <code><?= esc_html($HTACCESS['path']) ?></code>
        <?php if ($HTACCESS['exists']): ?>
            <span class="gpai-badge gpai-badge-ok" style="color:#00a32a;">Existe</span>
            <span style="font-size:12px;color:#666;">
                <?= size_format($HTACCESS['size']) ?> — <?= date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $HTACCESS['modified']) ?>
            </span>
        <?php else: ?>
            <span class="gpai-badge" style="color:#d63638;">No existe</span>
        <?php endif; ?>
        <?php if (!$htaccessWritable): ?>
            <span class="gpai-badge" style="color:#d63638;">Sin permisos de escritura</span>
        <?php endif; ?>
        <?php if ($HTACCESS['exists']): ?>
            <a href="<?= esc_url(admin_url('admin.php?page=' . GPAI_KEY . '_htaccess')) ?>" class="button">Ver .htaccess</a>
        <?php endif; ?>
    </div>

    <?php if (empty($REDIRECTS)): ?>
        <div class="gpai-redirect-empty">
            No hay redirecciones gestionadas por el plugin en .htaccess.
            Puedes añadir la primera con el botón «Añadir redirect» o creando una desde el tab «Reemplazar URL».
        </div>
    <?php endif; ?>

    <form method="post" id="gpai-redirects-form" data-nonce="<?= esc_attr(wp_create_nonce('gpai_nonce')) ?>" data-writable="<?= $htaccessWritable ? '1' : '0' ?>">
        <input type="hidden" name="save" value="gpai_redirects_update">
        <?php wp_nonce_field('gpai_redirects_save'); ?>

        <table class="widefat striped" id="gpai-redirects-table">
            <thead>
                <tr>
                    <th style="width:42%">URL Vieja</th>
                    <th style="width:42%">URL Nueva</th>
                    <th style="width:16%">Acciones</th>
                </tr>
            </thead>
            <tbody id="gpai-redirect-rows">
                <?php foreach ($REDIRECTS as $index => $redirect): ?>
                    <tr data-redirect-row>
                        <td>
                            <input
                                type="text"
                                name="redirects[<?= esc_attr($index) ?>][old]"
                                value="<?= esc_attr($redirect['old']) ?>"
                                placeholder="ej: my-old-url/sub-url/">
                        </td>
                        <td>
                            <input
                                type="text"
                                name="redirects[<?= esc_attr($index) ?>][new]"
                                value="<?= esc_attr($redirect['new']) ?>"
                                placeholder="ej: my-url/sub-url/">
                        </td>
                        <td>
                            <button type="button" class="button gpai-redirect-delete">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="gpai-redirect-msg" id="gpai-redirects-error" style="display:none;"></p>
        <p class="gpai-redirect-ok" id="gpai-redirects-ok" style="display:none;"></p>

        <div class="gpai-redirect-actions">
            <button type="button" class="button" id="gpai-redirect-add">
                Añadir redirect
            </button>
            <div class="submit">
                <button type="submit" class="button button-primary" id="gpai-redirects-submit" data-label="<?= esc_attr($htaccessWritable ? 'Guardar redirects' : 'Descargar .htaccess') ?>">
                    <?= esc_html($htaccessWritable ? 'Guardar redirects' : 'Descargar .htaccess') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tbody = document.getElementById('gpai-redirect-rows');
    var addBtn = document.getElementById('gpai-redirect-add');
    var form = document.getElementById('gpai-redirects-form');
    var errorEl = document.getElementById('gpai-redirects-error');
    var okEl = document.getElementById('gpai-redirects-ok');
    var submitBtn = document.getElementById('gpai-redirects-submit');
    var writable = form ? form.dataset.writable === '1' : true;
    var rowIndex = 0;

    (function() {
        var rows = tbody.querySelectorAll('tr');
        for (var i = 0; i < rows.length; i++) {
            var inputs = rows[i].querySelectorAll('input');
            for (var j = 0; j < inputs.length; j++) {
                var num = parseInt(inputs[j].name.replace(/[^0-9]/g, ''), 10);
                if (!isNaN(num) && num >= rowIndex) {
                    rowIndex = num + 1;
                }
            }
        }
    })();

    function row_html(index, oldVal, newVal) {
        return '<tr data-redirect-row>'
            + '<td><input type="text" name="redirects[' + index + '][old]" value="' + escapeAttr(oldVal) + '" placeholder="ej: my-old-url/sub-url/"></td>'
            + '<td><input type="text" name="redirects[' + index + '][new]" value="' + escapeAttr(newVal) + '" placeholder="ej: my-url/sub-url/"></td>'
            + '<td><button type="button" class="button gpai-redirect-delete">Eliminar</button></td>'
            + '</tr>';
    }

    function escapeAttr(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function closeMessage() {
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }
        if (okEl) {
            okEl.style.display = 'none';
            okEl.textContent = '';
        }
    }

    function showMessage(msg) {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
    }

    function showSuccess(msg) {
        if (!okEl) return;
        okEl.textContent = msg;
        okEl.style.display = 'block';
    }

    function downloadHtaccess(content) {
        var blob = new Blob([content], { type: 'application/octet-stream' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = '.htaccess';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function generateAndDownload() {
        if (!submitBtn) return;
        submitBtn.disabled = true;
        submitBtn.classList.remove('fwue-loader');
        submitBtn.textContent = 'Generando .htaccess...';

        var rows = tbody.querySelectorAll('tr[data-redirect-row]');
        var redirects = [];
        for (var i = 0; i < rows.length; i++) {
            var inputs = rows[i].querySelectorAll('input');
            redirects.push({ old: inputs[0].value.trim(), new: inputs[1].value.trim() });
        }

        var formData = new FormData();
        formData.append('action', 'gpai_htaccess_generate');
        formData.append('nonce', form.dataset.nonce);
        for (var j = 0; j < redirects.length; j++) {
            formData.append('redirects[' + j + '][old]', redirects[j].old);
            formData.append('redirects[' + j + '][new]', redirects[j].new);
        }

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                submitBtn.disabled = false;
                submitBtn.textContent = submitBtn.dataset.label || 'Descargar .htaccess';
                if (res.success && res.data.content) {
                    showMessage('');
                    showSuccess('Archivo .htaccess generado con ' + res.data.count + ' redirect(s). Súbelo manualmente al servidor para aplicar los redirects.');
                    downloadHtaccess(res.data.content);
                } else {
                    showSuccess('');
                    showMessage(res.data && res.data.message ? res.data.message : 'No se pudo generar el archivo .htaccess.');
                }
            })
            .catch(function() {
                submitBtn.disabled = false;
                submitBtn.textContent = submitBtn.dataset.label || 'Descargar .htaccess';
                showSuccess('');
                showMessage('Error de conexión. Intenta de nuevo.');
            });
    }

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            closeMessage();
            if (tbody) {
                tbody.insertAdjacentHTML('beforeend', row_html(rowIndex, '', ''));
                rowIndex++;
            }
        });
    }

    if (tbody) {
        tbody.addEventListener('click', function(e) {
            var target = e.target;
            if (target && target.classList && target.classList.contains('gpai-redirect-delete')) {
                closeMessage();
                var row = target.closest('tr[data-redirect-row]');
                if (row && row.parentNode === tbody) {
                    tbody.removeChild(row);
                }
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            closeMessage();
            var rows = tbody.querySelectorAll('tr[data-redirect-row]');
            for (var i = 0; i < rows.length; i++) {
                var inputs = rows[i].querySelectorAll('input');
                var oldVal = inputs[0] ? inputs[0].value.trim() : '';
                var newVal = inputs[1] ? inputs[1].value.trim() : '';
                if (oldVal === '' || newVal === '') {
                    var msg = 'Fila ' + (i + 1) + ': la URL vieja y la URL nueva son obligatorias.';
                    showMessage(msg);
                    if (submitBtn) submitBtn.classList.remove('fwue-loader');
                    if (oldVal === '' && inputs[0]) {
                        inputs[0].focus();
                    } else if (inputs[1]) {
                        inputs[1].focus();
                    }
                    e.preventDefault();
                    return false;
                }
            }

            if (!writable) {
                e.preventDefault();
                generateAndDownload();
                return false;
            }
            return true;
        });
    }
});
</script>