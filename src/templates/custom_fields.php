<?php

function DPAI_Custom_Fields($values = [], $valuesPrompt = [])
{
    return DPAI_Table_Fields(
        "customFields",
        [
            "Campo Personalizado",
            "Valor",
            "Prompt personalizado"
        ],
        $values,
        $valuesPrompt
    );
}
