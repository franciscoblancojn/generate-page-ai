<?php

use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;
use franciscoblancojn\wordpress_utils\FWUCollapse;

$DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
$respond_duplicates_pendding = null;

$GPAI_SAVE_OPTIONS_KEY = 'GPAI_SAVE_CONTENT_OPTIONS';
$default_save_options = [
    'custom_fields' => true,
    'custom_seo' => true,
    'global_fields' => true,
];
$save_options = get_option($GPAI_SAVE_OPTIONS_KEY, $default_save_options);
if (!is_array($save_options)) {
    $save_options = $default_save_options;
}

if (isset($_POST['save']) && $_POST['save'] == "duplicates_pendding") {
    if (GPAI_MODE_DEV && isset($_POST['submit_test']) && $_POST['submit_test'] == 'submit_test') {
        
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] == 'delete_all') {
        $GPAI_USE_DATA_DUPLICADOS->set([]);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $respond_duplicates_pendding = [
            "status" => "ok",
            "message" => "Eiminacion Exitosa.",
            'data' => [],
        ];
    }
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
    if (isset($_POST['submit_generate']) && $_POST['submit_generate'] == 'generate_all') {
        $respond_duplicates_pendding_all = $GPAI_USE_DATA_DUPLICADOS->generateAllVariations();
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
    }
    if (isset($_POST['submit_generate']) && $_POST['submit_generate'] != 'generate_all') {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_generate']);
        $post_id = (int)$post_id;
        $v = (int)$v;
        $respond_duplicates_pendding = $GPAI_USE_DATA_DUPLICADOS->generateVariation($post_id, $prompt, $v);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
    }
    if (isset($_POST['submit_save_custom_fields'])) {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_save_custom_fields']);
        $post_id = (int)$post_id;
        $v = (int)$v;

        $submitted_options = isset($_POST['gpai_save_options']) ? json_decode(stripslashes($_POST['gpai_save_options']), true) : [];
        if (is_array($submitted_options)) {
            $save_options = array_merge($default_save_options, $submitted_options);
            update_option($GPAI_SAVE_OPTIONS_KEY, $save_options);
        }

        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $DATA = $DUPLICADOS[$post_id]['variations'][$prompt][$v];

        if (!empty($save_options['custom_fields'])) {
            $customFields = $DATA['customFields'] ?? [];
            if (!empty($customFields)) {
                GPAI_CF::SET($post_id, $customFields);
                $respond_duplicates_pendding = [
                    "status" => "ok",
                    "message" => "Campos guardados.",
                    'data' => [],
                ];
            }
        }
        if (!empty($save_options['custom_seo'])) {
            $gpaiSeoFields = $DATA['gpaiSeoFields'] ?? [];
            if (!empty($gpaiSeoFields)) {
                GPAI_SEO::SET($post_id, $gpaiSeoFields);
                $respond_duplicates_pendding = [
                    "status" => "ok",
                    "message" => "Campos guardados.",
                    'data' => [],
                ];
            }
        }
        if (!empty($save_options['global_fields'])) {
            $globalFieldsPost = $DATA['globalFields'] ?? [];
            if (!empty($globalFieldsPost)) {
                foreach ($globalFieldsPost as $key => $value) {
                    update_post_meta($post_id, $key, wp_kses_post($value));
                }
                $respond_duplicates_pendding = [
                    "status" => "ok",
                    "message" => "Campos guardados.",
                    'data' => [],
                ];
            }
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
                type="button"
                class="button gpai-save-content-btn"
                data-value="<?= $post_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>">
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
    <input type="hidden" name="gpai_save_options" id="gpai-save-options-input" value="">
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
                                        $globalFields = $value['globalFields'] ?? [];
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
                                                ) .
                                                FWUCollapse::html(
                                                    "Global Fields",
                                                    GPAI_Custom_Fields($globalFields, false),
                                                    true
                                                ),
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

<div id="gpai-save-content-modal" style="display:none;">
    <div class="gpai-save-content-modal-overlay"></div>
    <div class="gpai-save-content-modal-box">
        <h3>Guardar Contenido en Pagina Inicial</h3>
        <p style="color:#666;font-size:13px;margin-bottom:14px;">Selecciona que datos deseas guardar:</p>
        <label class="gpai-save-content-option">
            <input type="checkbox" class="gpai-save-option" value="custom_fields" <?= !empty($save_options['custom_fields']) ? 'checked' : '' ?>>
            Custom Fields
        </label>
        <label class="gpai-save-content-option">
            <input type="checkbox" class="gpai-save-option" value="custom_seo" <?= !empty($save_options['custom_seo']) ? 'checked' : '' ?>>
            Custom SEO
        </label>
        <label class="gpai-save-content-option">
            <input type="checkbox" class="gpai-save-option" value="global_fields" <?= !empty($save_options['global_fields']) ? 'checked' : '' ?>>
            Global Fields
        </label>
        <div class="gpai-save-content-modal-actions">
            <button type="button" class="button gpai-save-modal-cancel">Cancelar</button>
            <button type="button" class="button button-primary gpai-save-modal-save" disabled>Guardar</button>
        </div>
    </div>
</div>

<style>
#gpai-save-content-modal {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}
#gpai-save-content-modal[style*="display:none"] { display: none !important; }
.gpai-save-content-modal-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
}
.gpai-save-content-modal-box {
    position: relative;
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    min-width: 320px;
    max-width: 420px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.gpai-save-content-modal-box h3 {
    margin: 0 0 4px;
    font-size: 16px;
}
.gpai-save-content-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    font-size: 14px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f1;
}
.gpai-save-content-option:last-of-type {
    border-bottom: none;
}
.gpai-save-content-option input[type="checkbox"] {
    margin: 0;
}
.gpai-save-content-modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f1;
}
</style>

<script>
(function() {
    var modal = document.getElementById('gpai-save-content-modal');
    var checkboxes = modal.querySelectorAll('.gpai-save-option');
    var saveBtn = modal.querySelector('.gpai-save-modal-save');
    var cancelBtn = modal.querySelector('.gpai-save-modal-cancel');
    var optionsInput = document.getElementById('gpai-save-options-input');
    var currentValue = null;
    var currentForm = null;

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.gpai-save-content-btn');
        if (!btn) return;

        currentValue = btn.getAttribute('data-value');
        currentForm = btn.closest('form');

        modal.style.display = 'flex';
        updateSaveBtn();
    });

    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        currentValue = null;
        currentForm = null;
    });

    modal.querySelector('.gpai-save-content-modal-overlay').addEventListener('click', function() {
        modal.style.display = 'none';
        currentValue = null;
        currentForm = null;
    });

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateSaveBtn);
    });

    function updateSaveBtn() {
        var checked = Array.from(checkboxes).some(function(cb) { return cb.checked; });
        saveBtn.disabled = !checked;
    }

    saveBtn.addEventListener('click', function() {
        var options = {};
        checkboxes.forEach(function(cb) {
            options[cb.value] = cb.checked;
        });
        optionsInput.value = JSON.stringify(options);

        if (currentForm && currentValue) {
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'submit_save_custom_fields';
            hidden.value = currentValue;
            currentForm.appendChild(hidden);
            currentForm.submit();
        }

        modal.style.display = 'none';
    });
})();
</script>

</form>