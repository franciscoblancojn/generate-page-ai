<?php

function DPAI_Custom_Yoast($values = [], $valuesPrompt = [])
{
    return DPAI_Table_Fields(
        "yoastFields",
        [
            "Campo de Yoast",
            "Valor",
            "Prompt personalizado"
        ],
        $values,
        $valuesPrompt
    );
}
