<?php

function GPAI_Global_Fields($values = [], $valuesPrompt = [], $overrides = [])
{
    ob_start();
?>
    <table class="form-table">
        <colgroup>
            <col style="width: 10%;">
            <col style="width: 35%;">
            <col style="width: 35%;">
            <col style="width: 20%;">
        </colgroup>
        <thead>
            <tr>
                <th>Variable Global</th>
                <th>Valor</th>
                <th>Prompt personalizado</th>
                <th>Sobreescribir</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($values as $key => $value) {
                $checked = isset($overrides[$key]) && $overrides[$key] == '1' ? 'checked' : '';
            ?>
                <tr>
                    <th scope="row">
                        <label for="globalFields_<?= $key ?>">
                            <?= esc_html($key) ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            id="globalFields_<?= $key ?>"
                            name="globalFields[<?= $key ?>]"
                            placeholder="<?= esc_attr($key) ?>"
                            style="min-height: 100px;"
                            class="large-text code"><?= esc_attr($value) ?></textarea>
                    </td>
                    <td>
                        <textarea
                            id="globalFields_prompt_<?= $key ?>"
                            name="globalFields_prompt[<?= $key ?>]"
                            placeholder="Prompt personalizado para <?= esc_attr($key) ?>."
                            class="large-text code"
                            style="min-height: 100px;"><?= isset($valuesPrompt[$key]) ? $valuesPrompt[$key] : "" ?></textarea>
                    </td>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="globalFields_override[<?= $key ?>]"
                                value="1"
                                <?= $checked ?>>
                            Sobreescribir
                        </label>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
<?php
    return ob_get_clean();
}
