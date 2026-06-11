<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;
use franciscoblancojn\wordpress_utils\FWUCollapse;
use franciscoblancojn\wordpress_utils\FWUExportImport;
use franciscoblancojn\wordpress_utils\FWUModal;

static $fwueAssets = false;
if (!$fwueAssets) {
    echo FWUModal::css() . FWUModal::js() . FWUExportImport::css() . FWUExportImport::js();
    $fwueAssets = true;
}

$CONFIG ??= [];
$post_id = $CONFIG['post_id'];
$customFields = [];
$gpaiSeoFields = [];
$respond_content = null;


if (isset($_POST['save']) && $_POST['save'] == "duplication") {
    $post_id = $_POST['post_id'] ?? $CONFIG['post_id'];
    if (isset($post_id)) {
        $is_save_post = isset($_POST['is_save_post']) && $_POST['is_save_post'] == "1";
        $is_save_custom_field = isset($_POST['is_save_custom_field']) && $_POST['is_save_custom_field'] == "1";
        $is_save_prompt = isset($_POST['is_save_prompt']) && $_POST['is_save_prompt'] == "1";
        $is_upgrade_prompts = isset($_POST['is_upgrade_prompts']) && $_POST['is_upgrade_prompts'] == "1";
        $is_generate_content = isset($_POST['is_generate_content']) && $_POST['is_generate_content'] == "1";
        if ($is_save_post) {
            if ($CONFIG['post_id'] != $post_id) {
                $CONFIG['customFields'] = [];
                $CONFIG['customFields_prompt'] = [];
                $CONFIG['gpaiSeoFields'] = [];
                $CONFIG['gpaiSeoFields_prompt'] = [];
            }
            $CONFIG['post_id'] = $post_id;
            $respond_content = [
                "status" => "ok",
                "message" => "Post Cargado.",
                'data' => [],
            ];
        }
        if ($is_save_custom_field || $is_upgrade_prompts || $is_generate_content) {
            $customFields = $_POST['customFields'] ?? [];
            if (!empty($customFields)) {
                GPAI_CF::SET($post_id, $customFields);
                $respond_content = [
                    "status" => "ok",
                    "message" => "Campos personalisados Guardados.",
                    'data' => [],
                ];
            }
            $gpaiSeoFields = $_POST['gpaiSeoFields'] ?? [];
            if (!empty($gpaiSeoFields)) {
                GPAI_SEO::SET($post_id, $gpaiSeoFields);
            }
            $CONFIG['customFields'] = $_POST['customFields'] ?? [];
            $CONFIG['gpaiSeoFields'] = $_POST['gpaiSeoFields'] ?? [];
            $CONFIG['customFields_prompt'] = array_map(function ($v) {
                return GPAI_CONTENT::cleanPromptText($v);
            }, $_POST['customFields_prompt'] ?? []);

            $CONFIG['gpaiSeoFields_prompt'] = array_map(function ($v) {
                return GPAI_CONTENT::cleanPromptText($v);
            }, $_POST['gpaiSeoFields_prompt'] ?? []);
        }
        if ($is_save_prompt || $is_upgrade_prompts || $is_generate_content) {
            $prompt = isset($_POST['prompt'])
                ? wp_unslash(trim($_POST['prompt']))
                : "";
            if (isset($prompt)) {
                $CONFIG['prompt'] = $prompt;
            }
        }
        if ($is_upgrade_prompts) {
            $PROMPT = GPAI_CONTENT::getPrompt($CONFIG);
            $respond_content = GPAI_PROMPT::getMejoraPrompt([
                'config' => $CONFIG,
                "prompt" => $PROMPT,
                "campos" => [
                    "PROMP BASE",
                    "PROMPTS PARA CAMPOS PERSONALIZADOS",
                    "PROMPTS PARA DATOS DE GPAI SEO"
                ],
            ]);
            if ($respond_content['status'] == 'ok') {
                $data = $respond_content['data'];
                if (isset($data["PROMP BASE"])) {
                    $CONFIG['prompt'] = $data["PROMP BASE"];
                }
                if (isset($data["PROMPTS PARA CAMPOS PERSONALIZADOS"])) {
                    foreach ($data["PROMPTS PARA CAMPOS PERSONALIZADOS"] as $key => $value) {
                        if ((isset($value) && !empty($value) && isset($CONFIG['customFields_prompt'][$key]))) {
                            $CONFIG['customFields_prompt'][$key] = $value;
                        }
                    }
                }
                if (isset($data["PROMPTS PARA DATOS DE GPAI SEO"])) {
                    foreach ($data["PROMPTS PARA DATOS DE GPAI SEO"] as $key => $value) {
                        if ((isset($value) && !empty($value) && isset($CONFIG['gpaiSeoFields_prompt'][$key]))) {
                            $CONFIG['gpaiSeoFields_prompt'][$key] = $value;
                        }
                    }
                }
            }
        }
        if ($is_generate_content) {
            $prompt = $_POST['prompt'];
            if (isset($prompt)) {
                $CONFIG['prompt'] = $prompt;
                $customFields = GPAI_CF::GET($post_id);
                $gpaiSeoFields = GPAI_SEO::GET($post_id);
                $CONFIG['customFields'] = $customFields;
                $CONFIG['gpaiSeoFields'] = $gpaiSeoFields;

                $GPAI_USE_DATA_GLOBAL_FIELDS = new GPAI_USE_DATA_GLOBAL_FIELDS();
                $CONFIG['globalFields'] = $GPAI_USE_DATA_GLOBAL_FIELDS->getAll();

                $respond_content = GPAI_CONTENT::getContent($CONFIG);
                if ($respond_content['status'] == 'ok') {
                    $POST_DATA = $DUPLICADOS[$post_id] ?? [];
                    $POST_DATA['post_id'] = $post_id;
                    $POST_DATA['customFields'] = $customFields;
                    $POST_DATA['gpaiSeoFields'] = $gpaiSeoFields;
                    $POST_DATA['variations'] ??= [];
                    $POST_DATA['variations'][$prompt] = $respond_content['data'];
                    $GPAI_USE_DATA_DUPLICADOS->setField($post_id, $POST_DATA);
                }
            }
        }
    }
    FWUSystemLog::add(GPAI_KEY, [
        'type' => "save_duplication",
        'data' => $_POST
    ]);
    $GPAI_USE_DATA_CONFIG->set($CONFIG);
}
if (isset($post_id)) {
    $customFields = GPAI_CF::GET($post_id);
    $gpaiSeoFields = GPAI_SEO::GET($post_id);
}

?>
<form method="post">
    <?php FWURespond::render($respond_content) ?>
    <input type="hidden" name="save" value="duplication">
    <table class="form-table">
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Post", "Selecciona la página a duplicar.") ?>
            </th>
            <td>
                <div class="content-btn">
                    <?php

                    ob_start();

                    wp_dropdown_pages([
                        'name'              => 'post_id',
                        'id'                => 'post_id',
                        'show_option_none'  => '-- Seleccionar --',
                        'option_none_value' => '',
                        'selected'          => $post_id,
                    ]);

                    $data = ob_get_clean();

                    $data = preg_replace_callback(
                        '/<option([^>]*)value="([^"]*)"([^>]*)>(.*?)<\/option>/si',
                        function ($m) {

                            $before = $m[1];
                            $value  = $m[2];
                            $after  = $m[3];
                            $label  = trim(strip_tags($m[4]));

                            // Mantener opción vacía igual
                            if ($value === '') {
                                return $m[0];
                            }

                            return sprintf(
                                '<option%svalue="%s"%s>#%s - %s</option>',
                                $before,
                                esc_attr($value),
                                $after,
                                esc_html($value),
                                esc_html($label)
                            );
                        },
                        $data
                    );

                    echo $data;
                    ?>

                    <button
                        type="submit"
                        name="is_save_post"
                        value="1"
                        class="button button-primary">
                        Cargar Post
                    </button>
                    <?php
                    if (isset($post_id)) {
                    ?>
                        <button
                            type="submit"
                            name="is_upgrade_prompts"
                            value="1"
                            class="button button-primary">
                            Guardar y Generar Prompts con IA
                        </button>
                        <a
                            href="<?= get_permalink($post_id) ?>"
                            target="_blank"
                            class="button ">
                            Ver Post
                        </a>
                        <a
                            href="<?= get_edit_post_link($post_id) ?>"
                            target="_blank"
                            class="button ">
                            Editar Post
                        </a>
                    <?php } ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
    if (isset($post_id)) {
        $parent_id = get_post_meta($post_id, GPAI_KEY . '_PARENT', true);
        $GPAI_USE_DATA_GLOBAL_FIELDS = new GPAI_USE_DATA_GLOBAL_FIELDS();
        $siteGlobalFields = $GPAI_USE_DATA_GLOBAL_FIELDS->getAll();
    ?>
        <?php if (!empty($parent_id)) { ?>
            <table class="form-table">
                <tr>
                    <th scope="row">GPAI Parent</th>
                    <td>
                        <a href="<?= get_edit_post_link($parent_id) ?>" target="_blank" class="button">
                            Editar Padre (#<?= $parent_id ?> - <?= esc_html(get_the_title($parent_id)) ?>)
                        </a>
                        <a href="<?= get_permalink($parent_id) ?>" target="_blank" class="button">
                            Ver Padre
                        </a>
                    </td>
                </tr>
            </table>
        <?php } ?>
        <?php FWUCollapse::render(
            "Custom Fields <code>{{...}}</code>",
            GPAI_Custom_Fields($customFields, $CONFIG['customFields_prompt']),
            true
        )
        ?>
        <?php if (!empty($siteGlobalFields)) { ?>
            <?php FWUCollapse::render(
                "Campos Globales del Sitio <code>{{...}}</code>",
                GPAI_Custom_Fields($siteGlobalFields, false),
                true
            )
            ?>
        <?php } ?>
        <?php
        $gpaiSeoContent = GPAI_Custom_Gpai_Seo_Grouped($gpaiSeoFields, $CONFIG['gpaiSeoFields_prompt'] ?? []);
        $gpaiSeoContent .= '<div class="content-btn" style="padding:12px 0 0;">';
        $gpaiSeoNonce = wp_create_nonce('gpai_seo_generate_' . $post_id);
        $gpaiSeoContent .= '<button type="button" class="button button-primary gpai-seo-generate-btn" data-post-id="' . esc_attr($post_id) . '" data-nonce="' . esc_attr($gpaiSeoNonce) . '">Generar SEO con IA</button>';
        $gpaiSeoContent .= '<a href="https://validator.schema.org/#url=' . urlencode(get_permalink($post_id)) . '" target="_blank" class="button">Validar SEO</a>';
        $gpaiSeoContent .= '<span class="gpai-seo-generate-status" style="margin-left:8px;font-style:italic;"></span>';
        $gpaiSeoContent .= '</div>';
        ?>
        <?php FWUCollapse::render("Gpai SEO", $gpaiSeoContent, true) ?>
        <div class="content-btn">
            <button
                type="submit"
                name="is_save_custom_field"
                value="1"
                class="button delete">
                Guardar Campos y Prompts Personalisados
            </button>
            <?php if ($post_id) { ?>
                <?= FWUExportImport::exportButtonHtml('gpai_export_post', ['post_id' => $post_id], 'post-' . $post_id . '-campos.json') ?>
                <?= FWUExportImport::importButtonHtml('gpai-modal-post') ?>
            <?php } ?>
        </div>
        <h3>Prompt para generar Contenido</h3>
        <textarea
            id="prompt"
            name="prompt"
            placeholder="Generar paginas duplicadas basandose en ...."
            class="large-text code"
            style="min-height: 200px;"
            rows="8"><?= $CONFIG['prompt'] ?></textarea>

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
    }
    ?>

</form>

<?= FWUExportImport::html('gpai-modal-post', 'Importar JSON &mdash; Post', 'gpai_import_post', ['post_id' => $post_id ?? ''], true) ?>
<?php
