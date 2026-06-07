<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;
use franciscoblancojn\wordpress_utils\FWUCollapse;

/**
 * Encode button value to safely pass template_id + prompt + index
 */
function gpai_encode_variation_key($template_id, $prompt, $v)
{
    return base64_encode(implode(GPAI_KEY_SEPARETE, [$template_id, $prompt, $v]));
}

/**
 * Decode variation key from button value
 */
function gpai_decode_variation_key($encoded)
{
    $decoded = base64_decode($encoded);
    if ($decoded === false) return null;
    $parts = explode(GPAI_KEY_SEPARETE, $decoded);
    if (count($parts) < 3) return null;
    return [
        'template_id' => (int)$parts[0],
        'prompt'      => $parts[1],
        'v'           => (int)$parts[2],
    ];
}

$T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();

if (isset($_POST['save']) && $_POST['save'] == "template_pendding") {
    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] == 'delete_all') {
        $GPAI_USE_DATA_TEMPLATES_CONTENT->set([]);
        $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
        $respond_procesar_plantilla = [
            "status" => "ok",
            "message" => "Eliminacion Exitosa.",
            'data' => [],
        ];
    }

    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] != 'delete_all' && $_POST['submit_delete'] != 'generate_all') {
        $key = gpai_decode_variation_key($_POST['submit_delete']);
        if ($key) {
            $GPAI_USE_DATA_TEMPLATES_CONTENT->deleteVariation($key['template_id'], $key['prompt'], $key['v']);
            $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
            $respond_procesar_plantilla = [
                "status" => "ok",
                "message" => "Eliminacion Exitosa.",
                'data' => [],
            ];
        }
    }

    if (isset($_POST['submit_save_defaults'])) {
        $key = gpai_decode_variation_key($_POST['submit_save_defaults']);
        if ($key) {
            $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
            $DATA = $T_CONTENT[$key['template_id']]['variations'][$key['prompt']][$key['v']] ?? [];

            if (!empty($DATA)) {
                unset($DATA['title']);
                GPAI_CF_TEMPLATE::SET($key['template_id'], $DATA);
                $respond_procesar_plantilla = [
                    "status" => "ok",
                    "message" => "Valores guardados como predeterminados de la plantilla.",
                    'data' => [],
                ];
            }
        }
    }

    if (isset($_POST['submit_create_template'])) {
        $key = gpai_decode_variation_key($_POST['submit_create_template']);
        if ($key) {
            $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();
            $DATA = $T_CONTENT[$key['template_id']]['variations'][$key['prompt']][$key['v']] ?? [];

            if (!empty($DATA)) {
                $title = $DATA['title'] ?? get_the_title($key['template_id']) . ' - Variacion';
                unset($DATA['title']);

                $original_post = get_post($key['template_id']);
                if ($original_post) {
                    $new_post_id = wp_insert_post([
                        'post_title'   => $title,
                        'post_content' => $original_post->post_content,
                        'post_status'  => 'publish',
                        'post_type'    => 'elementor_library',
                        'post_author'  => get_current_user_id(),
                    ]);

                    if ($new_post_id) {
                        $elementor_data = get_post_meta($key['template_id'], '_elementor_data', true);
                        if ($elementor_data) {
                            update_post_meta($new_post_id, '_elementor_data', wp_slash($elementor_data));
                        }

                        $meta_keys = ['_elementor_template_type', '_elementor_edit_mode', '_elementor_version'];
                        foreach ($meta_keys as $mk) {
                            $mv = get_post_meta($key['template_id'], $mk, true);
                            if ($mv) update_post_meta($new_post_id, $mk, $mv);
                        }

                        foreach ($DATA as $k => $v) {
                            update_post_meta($new_post_id, '_g_' . $k, wp_kses_post($v));
                        }

                        $GPAI_USE_DATA_TEMPLATES_CONTENT->deleteVariation($key['template_id'], $key['prompt'], $key['v']);
                        $T_CONTENT = $GPAI_USE_DATA_TEMPLATES_CONTENT->get();

                        $respond_procesar_plantilla = [
                            "status" => "ok",
                            "message" => "Plantilla creada exitosamente.",
                            'data' => [
                                'url' => admin_url('post.php?post=' . $new_post_id . '&action=elementor'),
                                'title' => $title,
                            ],
                        ];
                    } else {
                        $respond_procesar_plantilla = [
                            "status" => "error",
                            "message" => "Error al crear la plantilla.",
                            'data' => [],
                        ];
                    }
                }
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

    $previewUrl = admin_url('post.php?post=' . $template_id . '&action=elementor');
    foreach ($previewData as $key => $value) {
        $previewUrl = add_query_arg('global_' . $key, $value, $previewUrl);
    }

    $encoded_key = gpai_encode_variation_key($template_id, $prompt, $v);
?>
    <div class="content-btn" style="width: 100%;">
        <strong>
            <?= esc_html($title) ?>
        </strong>
        <div style="margin-left: auto; margin-right: 2rem;">
            <a
                href="<?= esc_url($previewUrl) ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="button delete">
                Previsualizar
            </a>
            <button
                type="submit"
                name="submit_delete"
                value="<?= esc_attr($encoded_key) ?>"
                class="button">
                Eliminar
            </button>
            <button
                type="submit"
                name="submit_save_defaults"
                value="<?= esc_attr($encoded_key) ?>"
                class="button">
                Guardar como Defaults
            </button>
            <button
                type="submit"
                name="submit_create_template"
                value="<?= esc_attr($encoded_key) ?>"
                class="button button-primary">
                Crear Plantilla
            </button>
        </div>
    </div>
    <br>
<?php
    return ob_get_clean();
}

?>
<form method="post">
    <?php FWURespond::render($respond_procesar_plantilla ?? null) ?>
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
                        <?php FWUTooltip::render("Plantilla", "Nombre de la plantilla Elementor.") ?>
                    </th>
                    <td>
                        <strong><?= get_the_title($template_id) ?></strong>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php FWUTooltip::render("Variaciones", "Variaciones de contenido generadas.") ?>
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
                                            <?php FWUCollapse::render(
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
