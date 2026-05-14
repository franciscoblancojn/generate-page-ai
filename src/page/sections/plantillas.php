<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$template_id = $TEMPLATE_CONFIG['template_id'] ?? null;
$globalFields = [];
$globalFields_prompt = $TEMPLATE_CONFIG['globalFields_prompt'] ?? [];

if (isset($_POST['save']) && $_POST['save'] == "template_fields") {
    $template_id = $_POST['template_id'] ?? $TEMPLATE_CONFIG['template_id'];

    if (isset($template_id)) {
        $is_save_template = isset($_POST['is_save_template']) && $_POST['is_save_template'] == "1";
        $is_save_fields = isset($_POST['is_save_fields']) && $_POST['is_save_fields'] == "1";
        $is_save_prompt = isset($_POST['is_save_prompt']) && $_POST['is_save_prompt'] == "1";
        $is_generate_content = isset($_POST['is_generate_content']) && $_POST['is_generate_content'] == "1";

        if ($is_save_template) {
            if ($TEMPLATE_CONFIG['template_id'] != $template_id) {
                $TEMPLATE_CONFIG['globalFields'] = [];
                $TEMPLATE_CONFIG['globalFields_prompt'] = [];
            }
            $TEMPLATE_CONFIG['template_id'] = $template_id;
            $respond_plantilla = [
                "status" => "ok",
                "message" => "Plantilla cargada.",
                'data' => [],
            ];
        }

        if ($is_save_prompt || $is_save_fields || $is_generate_content) {
            $globalFields = $_POST['globalFields'] ?? [];
            if (!empty($globalFields)) {
                GPAI_CF_TEMPLATE::SET($template_id, $globalFields);
                $respond_plantilla = [
                    "status" => "ok",
                    "message" => "Variables globales guardadas.",
                    'data' => [],
                ];
            }

            $TEMPLATE_CONFIG['globalFields'] = $globalFields;
            $TEMPLATE_CONFIG['globalFields_prompt'] = array_map(function ($v) {
                return GPAI_CONTENT::cleanPromptText($v);
            }, $_POST['globalFields_prompt'] ?? []);
        }

        if ($is_save_prompt) {
            $prompt = isset($_POST['prompt'])
                ? wp_unslash(trim($_POST['prompt']))
                : "";
            if (!empty($prompt)) {
                $TEMPLATE_CONFIG['prompt'] = $prompt;
                $respond_plantilla = [
                    "status" => "ok",
                    "message" => "Prompt guardado.",
                    'data' => [],
                ];
            }
        }

        if ($is_generate_content) {
            $prompt = isset($_POST['prompt'])
                ? wp_unslash(trim($_POST['prompt']))
                : "";

            if (!empty($prompt)) {
                $TEMPLATE_CONFIG['prompt'] = $prompt;

                $globalFields = GPAI_CF_TEMPLATE::GET($template_id);
                $TEMPLATE_CONFIG['globalFields'] = $globalFields;

                $respond_plantilla = GPAI_CONTENT::getContentTemplate($TEMPLATE_CONFIG);

                if ($respond_plantilla['status'] == 'ok') {
                    $DATA = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
                    $T_DATA = $DATA[$template_id] ?? [];
                    $T_DATA['template_id'] = $template_id;
                    $T_DATA['globalFields'] = $globalFields;
                    $T_DATA['variations'] ??= [];
                    $T_DATA['variations'][$prompt] = $respond_plantilla['data'];
                    $GPAI_USE_DATA_TEMPLATES_CONTENT->setField($template_id, $T_DATA);
                }
            } else {
                $respond_plantilla = [
                    "status" => "error",
                    "message" => "El prompt base es requerido para generar contenido.",
                    'data' => [],
                ];
            }
        }
    }

    FWUSystemLog::add(GPAI_KEY, [
        'type' => "save_template",
        'data' => $_POST
    ]);
    $GPAI_USE_DATA_TEMPLATES->set($TEMPLATE_CONFIG);
}

if (isset($template_id)) {
    $globalFields = GPAI_CF_TEMPLATE::GET($template_id);
}

?>
<form method="post">
    <?= GPAI_Respond($respond_plantilla ?? null) ?>
    <input type="hidden" name="save" value="template_fields">
    <table class="form-table">
        <tr>
            <th scope="row">
                <?= GPAI_Tooltip("Plantilla Elementor", "Selecciona una plantilla Elementor para gestionar sus variables globales {g{variable}}.") ?>
            </th>
            <td>
                <div class="content-btn">
                    <select name="template_id" id="template_id">
                        <option value="">-- Seleccionar --</option>
                        <?php
                        $templates = GPAI_CF_TEMPLATE::getTemplates();
                        foreach ($templates as $id => $title) {
                        ?>
                            <option value="<?= $id ?>" <?= $id == $template_id ? 'selected' : '' ?>>
                                <?= "#".$id ?>
                                -
                                <?= esc_html($title) ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                    <button
                        type="submit"
                        name="is_save_template"
                        value="1"
                        class="button button-primary">
                        Cargar Plantilla
                    </button>
                    <?php if (isset($template_id)) { ?>
                        <a
                            href="<?= esc_url(admin_url('post.php?post=' . $template_id . '&action=elementor')) ?>"
                            target="_blank"
                            class="button">
                            Ver Plantilla
                        </a>
                    <?php } ?>
                </div>
            </td>
        </tr>
    </table>

    <?php
    if (isset($template_id) && !empty($globalFields)) {
    ?>
        <?= GPAI_Collapse(
            "Variables Globales <code>{g{...}}</code>",
            GPAI_Table_Fields("globalFields", [
                "Variable Global",
                "Valor",
                "Prompt personalizado"
            ], $globalFields, $TEMPLATE_CONFIG['globalFields_prompt'] ?? []),
            true
        )
        ?>

        <div class="content-btn">
            <button
                type="submit"
                name="is_save_fields"
                value="1"
                class="button">
                Guardar Variables y Prompts
            </button>
            <button type="button" class="button" onclick="gpaiExport('gpai_export_template',{template_id:'<?= $template_id ?>'},'plantilla-<?= $template_id ?>-campos.json')">
                Exportar JSON
            </button>
            <button type="button" class="button" onclick="gpaiOpenModal('gpai-modal-template')">
                Importar JSON
            </button>
        </div>

        <h3>Prompt para generar contenido global</h3>
        <textarea
            id="prompt"
            name="prompt"
            placeholder="Ej: Genera variaciones de contenido para esta plantilla orientadas a..."
            class="large-text code"
            style="min-height: 200px;"
            rows="8"><?= $TEMPLATE_CONFIG['prompt'] ?? '' ?></textarea>

        <div class="content-btn">
            <button
                type="submit"
                name="is_save_prompt"
                value="1"
                class="button">
                Guardar Prompt
            </button>
            <button
                type="submit"
                name="is_generate_content"
                value="1"
                class="button button-primary">
                Guardar y Generar Contenido
            </button>
        </div>
    <?php
    } elseif (isset($template_id)) {
    ?>
        <p>No se encontraron variables globales <code>{g{...}}</code> en esta plantilla.</p>
    <?php
    }
    ?>
</form>

<div id="gpai-modal-template" class="gpai-modal">
    <div class="gpai-modal-content">
        <span class="gpai-modal-close" onclick="gpaiCloseModal('gpai-modal-template')">&times;</span>
        <h3>Importar JSON &mdash; Plantilla</h3>
        <p>
            <input type="file" class="gpai-import-file" accept=".json">
        </p>
        <textarea class="gpai-import-data" rows="12" placeholder="Pega el JSON aquí o selecciona un archivo..."></textarea>
        <div class="gpai-modal-actions">
            <button type="button" class="button button-primary gpai-import-btn" onclick="gpaiImport('gpai_import_template',{template_id:'<?= $template_id ?? '' ?>'},'gpai-modal-template',true)">Importar</button>
            <button type="button" class="button" onclick="gpaiCloseModal('gpai-modal-template')">Cancelar</button>
        </div>
    </div>
</div>
<?php
