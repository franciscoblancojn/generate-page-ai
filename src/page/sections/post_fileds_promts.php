<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$post_id = $CONFIG['post_id'];
$customFields = [];
$yoastFields = [];


if (isset($_POST['save']) && $_POST['save'] == "duplication") {
    $post_id = $_POST['post_id'] ?? $CONFIG['post_id'];
    if (isset($post_id)) {
        $is_save_post = isset($_POST['is_save_post']) && $_POST['is_save_post'] == "1";
        $is_save_custom_field = isset($_POST['is_save_post']) && $_POST['is_save_post'] == "1";
        $is_save_prompt = isset($_POST['is_save_prompt']) && $_POST['is_save_prompt'] == "1";
        $is_upgrade_promts = isset($_POST['is_upgrade_promts']) && $_POST['is_upgrade_promts'] == "1";
        $is_generate_content = isset($_POST['is_generate_content']) && $_POST['is_generate_content'] == "1";
        if ($is_save_post) {
            if ($CONFIG['post_id'] != $post_id) {
                $CONFIG['customFields_prompt'] = [];
                $CONFIG['yoastFields_prompt'] = [];
            }
            $CONFIG['post_id'] = $post_id;
            $respond_content = [
                "status" => "ok",
                "message" => "Post Cargado.",
                'data' => [],
            ];
        }
        if ($is_save_custom_field || $is_upgrade_promts || $is_generate_content) {
            $customFields = $_POST['customFields'] ?? [];
            if (!empty($customFields)) {
                DPAI_CF::SET($post_id, $customFields);
                $respond_content = [
                    "status" => "ok",
                    "message" => "Campos personalisados Guardados.",
                    'data' => [],
                ];
            }
            $yoastFields = $_POST['yoastFields'] ?? [];
            if (!empty($yoastFields)) {
                DPAI_YOAST::SET($post_id, $yoastFields);
                $respond_content = [
                    "status" => "ok",
                    "message" => "Campos personalisados Guardados.",
                    'data' => [],
                ];
            }
            $CONFIG['customFields'] = $_POST['customFields'] ?? [];
            $CONFIG['customFields_prompt'] = $_POST['customFields_prompt'] ?? [];
            $CONFIG['yoastFields'] = $_POST['yoastFields'] ?? [];
            $CONFIG['yoastFields_prompt'] = $_POST['yoastFields_prompt'] ?? [];
        }
        if ($is_save_prompt || $is_upgrade_promts || $is_generate_content) {
            $prompt = $_POST['prompt'];
            if (isset($prompt)) {
                $CONFIG['prompt'] = $prompt;
            }
        }
        if ($is_upgrade_promts) {
            $PROMPT = DPAI_CONTENT::getPrompt($CONFIG);
            $respond_content = DPAI_PROMPT::getMejoraPrompt([
                'config' => $CONFIG,
                "prompt" => $PROMPT,
                "campos" => [
                    "PROMP BASE",
                    "PROMPTS PARA CAMPOS PERSONALIZADOS",
                    "PROMPTS PARA DATOS DE YOAST SEO"
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
                if (isset($data["PROMPTS PARA DATOS DE YOAST SEO"])) {
                    foreach ($data["PROMPTS PARA DATOS DE YOAST SEO"] as $key => $value) {
                        if ((isset($value) && !empty($value) && isset($CONFIG['yoastFields_prompt'][$key]))) {
                            $CONFIG['yoastFields_prompt'][$key] = $value;
                        }
                    }
                }
            }
        }
        if ($is_generate_content) {
            $prompt = $_POST['prompt'];
            if (isset($prompt)) {
                $CONFIG['prompt'] = $prompt;
                $customFields = DPAI_CF::GET($post_id);
                $yoastFields = DPAI_YOAST::GET($post_id);
                $respond_content = DPAI_CONTENT::getContent($CONFIG);
                if ($respond_content['status'] == 'ok') {
                    $POST_DATA = $DUPLICADOS[$post_id] ?? [];
                    $POST_DATA['post_id'] = $post_id;
                    $POST_DATA['customFields'] = $customFields;
                    $POST_DATA['yoastFields'] = $yoastFields;
                    $POST_DATA['variations'] ??= [];
                    $POST_DATA['variations'][$prompt] = $respond_content['data'];
                    $DPAI_USE_DATA_DUPLICADOS->setField($post_id, $POST_DATA);
                }
            }
        }
    }
    FWUSystemLog::add(DPAI_KEY, [
        'type' => "save_duplication",
        'data' => $_POST
    ]);
    $DPAI_USE_DATA_CONFIG->set($CONFIG);
}
if (isset($post_id)) {
    $customFields = DPAI_CF::GET($post_id);
    $yoastFields = DPAI_YOAST::GET($post_id);
}

?>
<form method="post">
    <?= DPAI_Respond($respond_content) ?>
    <input type="hidden" name="save" value="duplication">
    <table class="form-table">
        <tr>
            <th scope="row">
                <?= DPAI_Tooltip("Post", "Selecciona la página a duplicar.") ?>
            </th>
            <td>
                <div class="content-btn">
                    <?php
                    wp_dropdown_pages([
                        'name'              => 'post_id',
                        'id'                => 'post_id',
                        'show_option_none'  => '-- Seleccionar --',
                        'option_none_value' => '',
                        'selected'          => $post_id,
                    ]);
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
                            name="is_upgrade_promts"
                            value="1"
                            class="button button-primary">
                            Guardar y Generar Prompts con IA
                        </button>
                    <?php } ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
    if (isset($post_id)) {
        $post = get_post_meta($post_id);
    ?>
        <?= DPAI_Collapse(
            "Custom Fields",
            DPAI_Custom_Fields($customFields, $CONFIG['customFields_prompt']),
            true
        )
        ?>
        <?= function_exists('YoastSEO') ?  DPAI_Collapse(
            " Yoast Seo",
            DPAI_Custom_Fields($yoastFields, $CONFIG['yoastFields_prompt'])
        )
            : ""
        ?>
        <div class="content-btn">
            <button
                type="submit"
                name="is_save_custom_field"
                value="1"
                class="button delete">
                Guardar Campos Personalisados
            </button>
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
<?php
