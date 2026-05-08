<?php

function GPAI_Custom_Fields($values = [], $valuesPrompt = [])
{
    return GPAI_Table_Fields(
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
