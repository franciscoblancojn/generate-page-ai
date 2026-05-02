<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;


if (isset($_POST['save']) && $_POST['save'] == "config") {
    FWUSystemLog::add(DPAI_KEY, [
        'type' => "save_config",
        'data' => $_POST
    ]);
    if (isset($_POST['apikey'])) {
        $CONFIG['apikey'] = $_POST['apikey'];
        $CONFIG['list_modelos'] = null;
    }
    $CONFIG['generate_img'] = isset($_POST['generate_img']);
    if (isset($_POST['modelo'])) {
        $CONFIG['modelo'] = $_POST['modelo'];
    }
    if (isset($CONFIG['apikey']) && $CONFIG['list_modelos'] == null) {
        $respond_config = DPAI_AI::getModels();
        if ($respond_config['status'] == "ok") {
            $CONFIG['list_modelos'] = $respond_config['data'] ?? [];
        }
    }
    $DPAI_USE_DATA_CONFIG->set($CONFIG);
}


?>
<form method="post">
    <?php
    if (isset($respond_config)) {
        getRespond($respond_config);
    }
    ?>
    <input type="hidden" name="save" value="config">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="apikey">
                    API KEY
                    <?= tooltip('Api key de Gemini para generar contenido con IA.') ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    id="apikey"
                    name="apikey"
                    placeholder="API KEY"
                    value="<?= $CONFIG['apikey'] ?>"
                    class="regular-text" />
            </td>
        </tr>

        <?php
        if (isset($CONFIG['list_modelos']) && count($CONFIG['list_modelos']) > 0) {
            $modelos = $CONFIG['list_modelos'];
            // Modelo actual o el primero de la lista
            $modeloActual = $CONFIG['modelo'] ?? ($modelos[0]['model'] ?? null);
        ?>
            <tr>
                <th scope="row">
                    <label for="modelo">
                        Modelo
                        <?= tooltip('Modelo de IA que se usa.') ?>
                    </label>
                </th>
                <td>
                    <select id="modelo" name="modelo" class="regular-text">
                        <?php foreach ($modelos as $model):
                            $value = $model['model'];
                            $label = $model['displayName'];
                        ?>
                            <option value="<?= esc_attr($value) ?>" <?= $modeloActual === $value ? 'selected' : '' ?>>
                                <?= esc_html($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        <?php
        }
        ?>

        <tr>
            <th scope="row">
                <label for="generate_img">
                    Generar images.
                    <?= tooltip('Permitir que Gemini genere la imagen principal para tus duplicados.') ?>
                </label>
            </th>
            <td>
                <input
                    type="checkbox"
                    id="generate_img"
                    name="generate_img"
                    placeholder="Generar images principales."
                    <?= $CONFIG['generate_img']  ? "checked" : "" ?>
                    class="regular-text" />

                <label for="generate_img">
                    Esto puede agotar tus tokens mas rapido.
                </label>
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
<?php
