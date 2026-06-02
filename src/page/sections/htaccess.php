<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$respond_htaccess = null;

if (isset($_POST['save']) && $_POST['save'] === 'htaccess_update') {
    if (!$HTACCESS['writable']) {
        $respond_htaccess = [
            'status' => 'error',
            'message' => 'El archivo .htaccess no tiene permisos de escritura.',
            'data' => [],
        ];
    } else {
        $content = $_POST['htaccess_content'] ?? '';

        if (!$HTACCESS['exists']) {
            $GPAI_USE_DATA_HTACCESS->backup();
        }

        $saved = $GPAI_USE_DATA_HTACCESS->save($content);
        if ($saved) {
            $respond_htaccess = [
                'status' => 'ok',
                'message' => '.htaccess guardado correctamente.',
                'data' => [],
            ];
        } else {
            $respond_htaccess = [
                'status' => 'error',
                'message' => 'Error al guardar .htaccess.',
                'data' => [],
            ];
        }
    }
    FWUSystemLog::add(GPAI_KEY, [
        'type' => 'save_htaccess',
        'data' => $_POST,
    ]);
    $HTACCESS = $GPAI_USE_DATA_HTACCESS->get();
}

?>
<?= GPAI_Respond($respond_htaccess) ?>

<div class="gpai-section">
    <form method="post">
        <input type="hidden" name="save" value="htaccess_update">
        <table class="form-table">
            <tr>
                <th scope="row">Archivo</th>
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
                        <?php if (!$HTACCESS['writable']): ?>
                            <span class="gpai-badge" style="color:#d63638;">Sin permisos de escritura</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">Contenido</th>
                <td>
                    <textarea name="htaccess_content" class="large-text code" style="min-height:400px;font-family:monospace;" rows="25" <?= !$HTACCESS['writable'] ? 'readonly' : '' ?>><?= esc_textarea($HTACCESS['content']) ?></textarea>
                </td>
            </tr>
        </table>
        <div class="content-btn" style="display:flex;gap:.5rem;align-items:center;">
            <button type="submit" class="button button-primary" <?= !$HTACCESS['writable'] ? 'disabled' : '' ?>>Guardar .htaccess</button>
            <?php if ($HTACCESS['exists']): ?>
                <a href="<?= esc_url(home_url('.htaccess')) ?>" target="_blank" class="button">Ver</a>
            <?php endif; ?>
        </div>
    </form>
</div>
