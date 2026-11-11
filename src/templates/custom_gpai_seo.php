<?php

function GPAI_Custom_Gpai_Seo($values = [], $valuesPrompt = [])
{
    return GPAI_Table_Fields(
        "gpaiSeoFields",
        [
            "Campo SEO",
            "Valor",
            "Prompt personalizado"
        ],
        $values,
        $valuesPrompt
    );
}

function GPAI_Custom_Gpai_Seo_Grouped($values = [], $valuesPrompt = [])
{
    $groups = GPAI_SEO::getGroups();
    $allFields = GPAI_SEO::getFields();
    $showPrompts = !empty($valuesPrompt);
    ob_start();
    foreach ($groups as $groupName => $fieldKeys) {
        $groupFields = [];
        foreach ($fieldKeys as $key) {
            if (!isset($allFields[$key])) continue;
            if (!array_key_exists($key, $values)) continue;
            $groupFields[$key] = $values[$key] ?? '';
        }
        if (empty($groupFields)) continue;
?>
        <details style="border:1px solid #ddd;border-radius:6px;background:#fafafa;margin-bottom:8px;" <?= $groupName === 'Principales' ? 'open' : '' ?>>
            <summary style="padding:8px 12px;font-weight:600;cursor:pointer;background:#f0f0f1;border-radius:6px 6px 0 0;user-select:none;">
                <?= esc_html($groupName) ?>
            </summary>
            <div style="padding:12px;">
                <?= GPAI_Table_Fields("gpaiSeoFields", ["Campo SEO", "Valor", "Prompt personalizado"], $groupFields, $showPrompts ? $valuesPrompt : false) ?>
            </div>
        </details>
<?php
    }
    return ob_get_clean();
}
