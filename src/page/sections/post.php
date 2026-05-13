<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$CONFIG ??= [];
$post_id = $CONFIG['post_id'];
$customFields = [];
$yoastFields = [];


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
                $CONFIG['yoastFields'] = [];
                $CONFIG['yoastFields_prompt'] = [];
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
            $yoastFields = $_POST['yoastFields'] ?? [];
            if (!empty($yoastFields)) {
                GPAI_YOAST::SET($post_id, $yoastFields);
                $respond_content = [
                    "status" => "ok",
                    "message" => "Campos personalisados Guardados.",
                    'data' => [],
                ];
            }
            $CONFIG['customFields'] = $_POST['customFields'] ?? [];
            $CONFIG['yoastFields'] = $_POST['yoastFields'] ?? [];
            $CONFIG['customFields_prompt'] = array_map(function ($v) {
                return GPAI_CONTENT::cleanPromptText($v);
            }, $_POST['customFields_prompt'] ?? []);

            $CONFIG['yoastFields_prompt'] = array_map(function ($v) {
                return GPAI_CONTENT::cleanPromptText($v);
            }, $_POST['yoastFields_prompt'] ?? []);

            $globalFieldsPost = $_POST['globalFields'] ?? [];
            if (!empty($globalFieldsPost)) {
                foreach ($globalFieldsPost as $tpl_key => $fields) {
                    $template_id = (int)str_replace('tpl_', '', $tpl_key);
                    if (!get_post($template_id)) continue;

                    foreach ((array)$fields as $key => $value) {
                        $override = isset($_POST['globalFields_override'][$tpl_key][$key]) && $_POST['globalFields_override'][$tpl_key][$key] == '1';
                        if ($override) {
                            update_post_meta($post_id, 'global_' . $key, sanitize_text_field($value));
                        } else {
                            delete_post_meta($post_id, 'global_' . $key);
                        }
                    }
                }
            }
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
                $customFields = GPAI_CF::GET($post_id);
                $yoastFields = GPAI_YOAST::GET($post_id);
                $CONFIG['customFields'] = $customFields;
                $CONFIG['yoastFields'] = $yoastFields;
                $respond_content = GPAI_CONTENT::getContent($CONFIG);
                if ($respond_content['status'] == 'ok') {
                    $POST_DATA = $DUPLICADOS[$post_id] ?? [];
                    $POST_DATA['post_id'] = $post_id;
                    $POST_DATA['customFields'] = $customFields;
                    $POST_DATA['yoastFields'] = $yoastFields;
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
    $yoastFields = GPAI_YOAST::GET($post_id);
}

$template_ids_detected = [];
$globalFieldsByTemplate = [];
$globalOverridesByTemplate = [];
$globalPromptsByTemplate = [];
if (isset($post_id)) {
    $template_ids_detected = GPAI_CF_TEMPLATE::getPostTemplates($post_id);
    $TEMPLATE_CONFIG_DATA = get_option(GPAI_TEMPLATES_CONFIG, []);

    foreach ($template_ids_detected as $template_id) {
        $templateVars = GPAI_CF_TEMPLATE::GET($template_id);
        $fields = [];
        $overrides = [];

        foreach ($templateVars as $key => $defaultVal) {
            $postVal = get_post_meta($post_id, 'global_' . $key, true);
            if ($postVal !== '') {
                $fields[$key] = $postVal;
                $overrides[$key] = '1';
            } else {
                $fields[$key] = $defaultVal;
                $overrides[$key] = '0';
            }
        }

        $globalFieldsByTemplate[$template_id] = $fields;
        $globalOverridesByTemplate[$template_id] = $overrides;
        $globalPromptsByTemplate[$template_id] = $TEMPLATE_CONFIG_DATA[$template_id]['globalFields_prompt'] ?? [];
    }
}

?>
<form method="post">
    <?= GPAI_Respond($respond_content) ?>
    <input type="hidden" name="save" value="duplication">
    <table class="form-table">
        <tr>
            <th scope="row">
                <?= GPAI_Tooltip("Post", "Selecciona la página a duplicar.") ?>
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
                    <?php } ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
    if (isset($post_id)) {
        $post = get_post_meta($post_id);
    ?>
        <?= GPAI_Collapse(
            "Custom Fields <code>{{...}}</code>",
            GPAI_Custom_Fields($customFields, $CONFIG['customFields_prompt']),
            true
        )
        ?>
        <?php foreach ($template_ids_detected as $tpl_id) {
            $fields = $globalFieldsByTemplate[$tpl_id] ?? [];
            if (empty($fields)) continue;
        ?>
            <?= GPAI_Collapse(
                "Campos Globales <code>{g{...}}</code> (" . get_the_title($tpl_id) . ")",
                GPAI_Global_Fields($fields, $globalPromptsByTemplate[$tpl_id], $globalOverridesByTemplate[$tpl_id], 'tpl_' . $tpl_id),
                true
            )
            ?>
        <?php } ?>
        <?= function_exists('YoastSEO') ?  GPAI_Collapse(
            "Yoast Seo",
            GPAI_Custom_Fields($yoastFields, $CONFIG['yoastFields_prompt'])
        )
            : ""
        ?>
        <div class="content-btn">
            <button
                type="submit"
                name="is_save_custom_field"
                value="1"
                class="button delete">
                Guardar Campos y Prompts Personalisados
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
