<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;

$GPAI_USE_DATA_GLOBAL_FIELDS = new GPAI_USE_DATA_GLOBAL_FIELDS();
$GLOBAL_FIELDS = $GPAI_USE_DATA_GLOBAL_FIELDS->getAll();

$edit_key = isset($_GET['edit']) ? sanitize_key($_GET['edit']) : '';
$editing_value = '';
if ($edit_key && isset($GLOBAL_FIELDS[$edit_key])) {
    $editing_value = $GLOBAL_FIELDS[$edit_key];
}

if (isset($_POST['save']) && $_POST['save'] === 'campo_global') {
    $key = isset($_POST['field_key']) ? sanitize_key($_POST['field_key']) : '';
    $value = isset($_POST['field_value']) ? $_POST['field_value'] : '';

    if ($key !== '') {
        $sanitized = wp_kses_post($value);
        $GPAI_USE_DATA_GLOBAL_FIELDS->setField($key, $sanitized);

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_GLOBAL_FIELD_SAVE',
            'key' => $key,
        ]);

        $respond = [
            'status' => 'ok',
            'message' => "Campo global «{$key}» guardado correctamente."
        ];

        $edit_key = '';
        $editing_value = '';
        $GLOBAL_FIELDS = $GPAI_USE_DATA_GLOBAL_FIELDS->getAll();
    }
}

if (isset($_POST['delete']) && $_POST['delete'] === 'campo_global') {
    $key = isset($_POST['field_key']) ? sanitize_key($_POST['field_key']) : '';

    if ($key !== '' && isset($GLOBAL_FIELDS[$key])) {
        $GPAI_USE_DATA_GLOBAL_FIELDS->deleteField($key);

        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_GLOBAL_FIELD_DELETE',
            'key' => $key,
        ]);

        $respond = [
            'status' => 'ok',
            'message' => "Campo global «{$key}» eliminado correctamente."
        ];
    }
}

?>

<?php FWURespond::render($respond) ?>

<form method="post">
    <input type="hidden" name="save" value="campo_global">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="field_key">Nombre del campo</label>
            </th>
            <td>
                <input
                    type="text"
                    id="field_key"
                    name="field_key"
                    value="<?= esc_attr($edit_key) ?>"
                    placeholder="ej: telefono_contacto"
                    class="regular-text code"
                    <?= $edit_key ? 'readonly' : '' ?>>
                <p class="description">
                    Usa <code>{{nombre_del_campo}}</code> en tus paginas para que sea reemplazado por este valor.
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="field_value">Valor</label>
            </th>
            <td>
                <textarea
                    id="field_value"
                    name="field_value"
                    placeholder="Valor del campo global (puede contener HTML)"
                    class="large-text code"
                    style="min-height: 150px;"><?= esc_textarea($editing_value) ?></textarea>
            </td>
        </tr>
    </table>
    <div class="content-btn">
        <button
            type="submit"
            name="submit"
            value="Guardar"
            class="button button-primary">
            <?= $edit_key ? 'Actualizar Campo Global' : 'Agregar Campo Global' ?>
        </button>
        <?php if ($edit_key): ?>
            <a href="<?= admin_url('admin.php?page=' . GPAI_KEY . '_campos_globales') ?>" class="button">
                Cancelar
            </a>
        <?php endif; ?>
    </div>
</form>

<h2>Campos Globales Existentes</h2>

<?php if (empty($GLOBAL_FIELDS)): ?>
    <p>Aun no hay campos globales definidos.</p>
<?php else: ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 25%;">Clave</th>
                <th>Valor</th>
                <th style="width: 15%;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($GLOBAL_FIELDS as $key => $value): ?>
                <tr>
                    <td>
                        <strong><?= esc_html($key) ?></strong>
                        <br>
                        <code>{{<?= esc_html($key) ?>}}</code>
                    </td>
                    <td>
                        <div style="max-height: 100px; overflow-y: auto;">
                            <?= wp_kses_post($value) ?>
                        </div>
                    </td>
                    <td>
                        <a href="<?= admin_url('admin.php?page=' . GPAI_KEY . '_campos_globales&edit=' . urlencode($key)) ?>"
                           class="button button-small">
                            Editar
                        </a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete" value="campo_global">
                            <input type="hidden" name="field_key" value="<?= esc_attr($key) ?>">
                            <button
                                type="submit"
                                class="button button-small"
                                onclick="return confirm('¿Eliminar el campo global «<?= esc_js($key) ?>»?')">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
