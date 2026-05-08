<?php

function GPAI_Custom_Yoast($values = [], $valuesPrompt = [])
{
    return GPAI_Table_Fields(
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
