<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();

if (isset($_POST['save']) && $_POST['save'] == "template_pendding") {
    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] == 'delete_all') {
        $GPAI_USE_DATA_TEMPLATES_CONTENT->set([]);
        $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
        $respond = [
            "status" => "ok",
            "message" => "Eliminacion Exitosa.",
            'data' => [],
        ];
    }

    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] != 'delete_all' && $_POST['submit_delete'] != 'generate_all') {
        [$template_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_delete']);
        $template_id = (int)$template_id;
        $v = (int)$v;
        $GPAI_USE_DATA_TEMPLATES_CONTENT->deleteVariation($template_id, $prompt, $v);
        $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
        $respond = [
            "status" => "ok",
            "message" => "Eliminacion Exitosa.",
            'data' => [],
        ];
    }

    if (isset($_POST['submit_save_defaults'])) {
        [$template_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_save_defaults']);
        $template_id = (int)$template_id;
        $v = (int)$v;
        $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
        $DATA = $T_CONTENT[$template_id]['variations'][$prompt][$v];

        if (!empty($DATA)) {
            unset($DATA['title']);
            GPAI_CF_TEMPLATE::SET($template_id, $DATA);
            $respond = [
                "status" => "ok",
                "message" => "Valores guardados como predeterminados de la plantilla.",
                'data' => [],
            ];
        }
    }

    if (isset($_POST['submit_create_page'])) {
        [$template_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_create_page']);
        $template_id = (int)$template_id;
        $v = (int)$v;
        $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
        $DATA = $T_CONTENT[$template_id]['variations'][$prompt][$v];

        if (!empty($DATA)) {
            $title = $DATA['title'] ?? get_the_title($template_id) . ' - Variacion';
            unset($DATA['title']);

            $new_post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => '',
                'post_status'  => 'draft',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id(),
            ]);

            if ($new_post_id) {
                foreach ($DATA as $key => $value) {
                    update_post_meta($new_post_id, '_g_' . $key, wp_kses_post($value));
                }
                update_post_meta($new_post_id, GPAI_KEY . '_TEMPLATE_PARENT', $template_id);

                $respond = [
                    "status" => "ok",
                    "message" => "Pagina creada exitosamente.",
                    'data' => [
                        'url' => get_permalink($new_post_id),
                        'title' => $title,
                    ],
                ];
            } else {
                $respond = [
                    "status" => "error",
                    "message" => "Error al crear la pagina.",
                    'data' => [],
                ];
            }
        }
    }

    FWUSystemLog::add(GPAI_KEY, [
        'type' => "process_template_pendding",
        'data' => $_POST
    ]);
}

function getHeadCollapseTemplate($DATA, $template_id, $prompt, $v)
{
    ob_start();
    $title = $DATA['title'] ?? 'Variacion';
    $previewData = $DATA;
    unset($previewData['title']);
?>
    <div class="content-btn" style="width: 100%;">
        <strong>
            <?= esc_html($title) ?>
        </strong>
        <div style="margin-left: auto; margin-right: 2rem;">
            <button
                type="submit"
                name="submit_delete"
                value="<?= $template_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button">
                Eliminar
            </button>
            <button
                type="submit"
                name="submit_save_defaults"
                value="<?= $template_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button">
                Guardar como Defaults
            </button>
            <button
                type="submit"
                name="submit_create_page"
                value="<?= $template_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button button-primary">
                Crear Pagina
            </button>
        </div>
    </div>
    <br>
<?php
    return ob_get_clean();
}

?>
<form method="post">
    <?= GPAI_Respond($respond ?? null) ?>
    <input type="hidden" name="save" value="template_pendding">

    <?php if (count($T_CONTENT) == 0) { ?>
        <h3>No tienes variaciones de plantillas generadas.</h3>
    <?php } else { ?>
        <div class="content-title-btn">
            <h3>Variaciones de Plantillas Generadas</h3>
            <div class="content-btn">
                <button
                    type="submit"
                    name="submit_delete"
                    value="delete_all"
                    class="button button-primary">
                    Eliminar todos
                </button>
            </div>
        </div>

        <table class="form-table">
            <?php
            foreach ($T_CONTENT as $template_id => $content) {
                $variations = $content['variations'] ?? [];
            ?>
                <tr>
                    <th scope="row">
                        <?= GPAI_Tooltip("Plantilla", "Nombre de la plantilla Elementor.") ?>
                    </th>
                    <td>
                        <strong><?= get_the_title($template_id) ?></strong>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?= GPAI_Tooltip("Variaciones", "Variaciones de contenido generadas.") ?>
                    </th>
                </tr>
                <tr>
                    <td colspan="2">
                        <table class="form-table">
                            <?php
                            foreach ($variations as $prompt => $variation) {
                            ?>
                                <tr>
                                    <th scope="row">
                                        <label>Prompt</label>
                                    </th>
                                    <td>
                                        <i>"<?= esc_html($prompt) ?>"</i>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <?php
                                        foreach ($variation as $v => $DATA) {
                                            $displayData = $DATA;
                                            unset($displayData['title']);
                                        ?>
                                            <?= GPAI_Collapse(
                                                getHeadCollapseTemplate($DATA, $template_id, $prompt, $v),
                                                GPAI_Custom_Fields($displayData, false),
                                                true
                                            )
                                            ?>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php } ?>
</form>
<?php
