<?php

function DPAI_Table_Fields($KEY, $COLS = [], $fields = [], $valuesPrompt = [])
{
    ob_start();
?>
    <table class="form-table">
        <colgroup>
            <col style="width: 10%;">
            <col style="width: 40%;">
            <col style="width: 40%;">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <?= $COLS[0] ?>
                </th>
                <th>
                    <?= $COLS[1] ?>
                </th>
                <th>
                    <?= $COLS[2] ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($fields as $key => $value) {
            ?>
                <tr>
                    <th scope="row">
                        <label for="<?= $key ?>">
                            <?= $key ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            id="<?= $KEY ?>_<?= $key ?>"
                            name="<?= $KEY ?>[<?= $key ?>]"
                            placeholder="<?= $key ?>"
                            style="min-height: 100px;"
                            class="large-text code"><?= esc_attr($value) ?></textarea>
                    </td>
                    <td>
                        <textarea
                            id="<?= $KEY ?>_prompt_<?= $key ?>"
                            name="<?= $KEY ?>_prompt[_<?= $key ?>]"
                            placeholder="Promt personalizado para <?= $key ?>."
                            class="large-text code"
                            style="min-height: 100px;"><?= isset($valuesPrompt[$key]) ? $valuesPrompt[$key] : "" ?></textarea>
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
