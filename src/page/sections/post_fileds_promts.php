<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

$post_id = $CONFIG['post_id'];
$customFields = [];
$yoastFields = [];
if (isset($_POST['save']) && $_POST['save'] == "duplication") {
    $post_id = $_POST['post_id'] ?? $CONFIG['post_id'];
    if (isset($_POST['save_post']) && $_POST['save_post'] == 1 && isset($post_id)) {
        $CONFIG['post_id'] = $post_id;
        $CONFIG['customFields_prompt'] = [];
        $CONFIG['yoastFields_prompt'] = [];
        $respond_duplicados = [
            "status" => "ok",
            "message" => "Post Cargado.",
            'data' => [],
        ];
    }
    if (isset($_POST['set_custom_field']) && $_POST['set_custom_field'] == "1" && isset($post_id)) {
        $customFields = $_POST['customFields'] ?? [];
        if (!empty($customFields)) {
            DPAI_CF::SET($post_id, $customFields);
            $respond_duplicados = [
                "status" => "ok",
                "message" => "Campos personalisados Guardados.",
                'data' => [],
            ];
        }
        $yoastFields = $_POST['yoastFields'] ?? [];
        if (!empty($yoastFields)) {
            DPAI_YOAST::SET($post_id, $yoastFields);
            $respond_duplicados = [
                "status" => "ok",
                "message" => "Campos personalisados Guardados.",
                'data' => [],
            ];
        }
        $CONFIG['customFields_prompt'] = $_POST['customFields_prompt'] ?? [];
        $CONFIG['yoastFields_prompt'] = $_POST['yoastFields_prompt'] ?? [];
    }
    if (isset($_POST['generate_duplicate']) && $_POST['generate_duplicate'] == "1" && isset($post_id)) {
        $prompt = $_POST['prompt'];
        if (isset($prompt)) {
            $CONFIG['prompt'] = $prompt;
            $customFields = DPAI_CF::GET($post_id);
            $yoastFields = DPAI_YOAST::GET($post_id);
            $respond_duplicados = DPAI_DUPLICADOS::getDuplicados($post_id, $prompt, $customFields, $yoastFields);
            if ($respond_duplicados['status'] == 'ok') {
                $POST_DATA = $DUPLICADOS[$post_id] ?? [];
                $POST_DATA['post_id'] = $post_id;
                $POST_DATA['customFields'] = $customFields;
                $POST_DATA['yoastFields'] = $yoastFields;
                $POST_DATA['variations'] ??= [];
                $POST_DATA['variations'][$prompt] = $respond_duplicados['data'];
                $DPAI_USE_DATA_DUPLICADOS->setField($post_id, $POST_DATA);
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
    <?=DPAI_Respond($respond_duplicados)?>
    <input type="hidden" name="save" value="duplication">
    <table class="form-table">
        <tr>
            <th scope="row">
                <?= DPAI_Tooltip("Post", "Selecciona la página a duplicar.") ?>
            </th>
            <td>
                <?php
                wp_dropdown_pages([
                    'name'              => 'post_id',
                    'id'                => 'post_id',
                    'show_option_none'  => '-- Seleccionar --',
                    'option_none_value' => '',
                    'selected'          => $post_id,
                ]);
                ?>
            </td>
        </tr>
    </table>
    <button
        type="submit"
        name="save_post"
        value="1"
        class="button button-primary">
        Cargar Post
    </button>
    <br />
    <br />
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
                name="set_custom_field"
                value="1"
                class="button delete">
                Guardar Campos Personalisados
            </button>
        </div>
        <h3>Prompt para generar Duplicados</h3>
        <textarea
            id="prompt"
            name="prompt"
            placeholder="Generar paginas duplicadas basandose en ...."
            class="large-text code"
            style="min-height: 200px;"
            rows="8"><?= $CONFIG['prompt'] ?></textarea>

        <button
            type="submit"
            name="generate_duplicate"
            value="1"
            class="button">
            Generar Duplicados
        </button>
    <?php
    }
    ?>

</form>
<?php
