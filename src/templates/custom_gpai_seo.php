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
