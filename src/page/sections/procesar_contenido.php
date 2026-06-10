<?php

use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;
use franciscoblancojn\wordpress_utils\FWUCollapse;
// var_dump([
//     "is_user_admin" => current_user_can( 'manage_options' )
// ]);
$DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
$respond_duplicates_pendding = null;

if (isset($_POST['save']) && $_POST['save'] == "duplicates_pendding") {
    //PRUEBAS:
    if (GPAI_MODE_DEV && isset($_POST['submit_test']) && $_POST['submit_test'] == 'submit_test') {
        
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    //OK: ELIMINAR TODOS
    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] == 'delete_all') {
        $GPAI_USE_DATA_DUPLICADOS->set([]);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $respond_duplicates_pendding = [
            "status" => "ok",
            "message" => "Eiminacion Exitosa.",
            'data' => [],
        ];
    }
    //OK: ELIMINAR UNA VARIAION
    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] != 'generate_all') {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_delete']);
        $post_id = (int)$post_id;
        $v = (int)$v;
        $GPAI_USE_DATA_DUPLICADOS->deleteVariation($post_id, $prompt, $v);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $respond_duplicates_pendding = [
            "status" => "ok",
            "message" => "Eiminacion Exitosa.",
            'data' => [],
        ];
    }
    // OK: GENERAR TODOS
    if (isset($_POST['submit_generate']) && $_POST['submit_generate'] == 'generate_all') {
        $respond_duplicates_pendding_all = $GPAI_USE_DATA_DUPLICADOS->generateAllVariations();
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
    }
    // OK: GENERAR UNO
    if (isset($_POST['submit_generate']) && $_POST['submit_generate'] != 'generate_all') {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_generate']);
        $post_id = (int)$post_id;
        $v = (int)$v;
        $respond_duplicates_pendding = $GPAI_USE_DATA_DUPLICADOS->generateVariation($post_id, $prompt, $v);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
    }
    // OK: GENERAR UNO
    if (isset($_POST['submit_save_custom_fields'])) {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_save_custom_fields']);
        $post_id = (int)$post_id;
        $v = (int)$v;
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $DATA = $DUPLICADOS[$post_id]['variations'][$prompt][$v];
        $customFields = $DATA['customFields'] ?? [];
        if (!empty($customFields)) {
            GPAI_CF::SET($post_id, $customFields);
            $respond_duplicates_pendding = [
                "status" => "ok",
                "message" => "Campos personalisados Guardados.",
                'data' => [],
            ];
        }
        $gpaiSeoFields = $DATA['gpaiSeoFields'] ?? [];
        if (!empty($gpaiSeoFields)) {
            GPAI_SEO::SET($post_id, $gpaiSeoFields);
            $respond_duplicates_pendding = [
                "status" => "ok",
                "message" => "Campos personalisados Guardados.",
                'data' => [],
            ];
        }
        if(isset($respond_duplicates_pendding['status']) && $respond_duplicates_pendding['status'] === "ok"){
            ?>
            <script>
                document.querySelector('[data-tab="<?= $TAGS[1]['key'] ?>"]').click()
                setTimeout(() => {
                    window.location.reload()
                }, 500);
            </script>
            <?php 
            exit;
        }
        
    }
}

function getHeadCollapseVariation($value, $customFields, $post_id, $prompt, $v)
{
    ob_start();
?>
    <div class="content-btn" style="width: 100%;">
        <strong>
            <?= $value['title'] ?>
        </strong>
        <div style="margin-left: auto;margin-right:2rem;">
            <?php
            $url = add_query_arg($customFields, get_permalink($post_id));
            ?>

            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="button delete">
                Previsualizar
            </a>
            <button
                type="submit"
                name="submit_delete"
                value="<?= $post_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button button-primary">
                Eliminar
            </button>

            <button
                type="submit"
                name="submit_generate"
                value="<?= $post_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button button-primary">
                Generar Nueva Pagina
            </button>
            <button
                type="submit"
                name="submit_save_custom_fields"
                value="<?= $post_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button">
                Guardar Contenido en Pagina Inicial
            </button>
        </div>
    </div>
    <br>
<?php
    return ob_get_clean();
}

?>
<form method="post">
    <?php FWURespond::render($respond_duplicates_pendding) ?>
    <?php
    if (isset($respond_duplicates_pendding_all)) {
        if ($respond_duplicates_pendding_all['status'] == 'error') {
            FWURespond::render($respond_duplicates_pendding_all);
        } else {
            foreach ($respond_duplicates_pendding_all['data'] as $key => $respond) {
                FWURespond::render($respond);
            }
        }
    }
    ?>
    <input type="hidden" name="save" value="duplicates_pendding">
    <?php
    if (count($DUPLICADOS) == 0) {
    ?>
        <h3>
            No tienes contenido generado.
        </h3>
        <?php
        if (GPAI_MODE_DEV) {
        ?>
            <button
                type="submit"
                name="submit_test"
                value="submit_test"
                class="button button-primary">
                Test
            </button>
        <?php
        }
        ?>
    <?php
    } else {
    ?>
        <div class="content-title-btn">
            <h3>
                Lista de contendigo generado.
            </h3>
            <div class="content-btn">
                <?php
                if (GPAI_MODE_DEV) {
                ?>
                    <button
                        type="submit"
                        name="submit_test"
                        value="submit_test"
                        class="button button-primary">
                        Test
                    </button>
                <?php
                }
                ?>
                <button
                    type="submit"
                    name="submit_delete"
                    value="delete_all"
                    class="button button-primary">
                    Eliminar todos
                </button>
                <button
                    type="submit"
                    name="submit_generate"
                    value="generate_all"
                    class="button button-primary">
                    Generar todos
                </button>
            </div>
        </div>

    <?php
    }

    ?>
    <table class="form-table">
        <?php
        foreach ($DUPLICADOS as $post_id => $duplication) {
            $customFields = $duplication['customFields'];
            $variations = $duplication['variations'];
        ?>
            <tr>
                <th scope="row">
                    <?php FWUTooltip::render(" Post Name", "Nombre de la pagina a generar.") ?>
                </th>
                <td>
                    <?= get_the_title($post_id); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php FWUTooltip::render("Variaciones", "Variacion de pagina a generar.") ?>
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
                                    <label>
                                        Prompt
                                    </label>
                                </th>
                                <td>
                                    <i>
                                        "<?= $prompt ?>"
                                    </i>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <?php
                                    foreach ($variation as $v => $value) {
                                        $customFields = $value['customFields'];
                                        $gpaiSeoFields = $value['gpaiSeoFields'] ?? [];
                                    ?>
                                        <?php FWUCollapse::render(
                                            getHeadCollapseVariation($value, $customFields, $post_id, $prompt, $v),
                                            "" .
                                                FWUCollapse::html(
                                                    "Custom Fields",
                                                    GPAI_Custom_Fields($customFields, false),
                                                    true
                                                ) .
                                                FWUCollapse::html(
                                                    "Custom SEO",
                                                    GPAI_Custom_Gpai_Seo($gpaiSeoFields, false),
                                                    true
                                                ) 
                                                ,
                                            true
                                        )
                                        ?>
                                    <?php
                                    }
                                    ?>

                                </td>
                            </tr>
                    </table>
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

</form>