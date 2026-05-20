<?php

$types = [
    'content' => 'Generar contenido (getPrompt)',
    // 'content_img' => 'Generar imagen (getPromptImg)', //de momento no esta disponible
    'template' => 'Variables globales (getContentTemplate)',
    'seo' => 'SEO (getSEOBasePromptDefault)',
    'html' => 'Optimización HTML (getHTMLBasePromptDefault)',
];

$placeholders = [
    'content' => [
        '{{title}}' => 'Titulo de la pagina',
        '{{customFields}}' => 'JSON con campos personalizados',
        '{{customFields_prompt}}' => 'JSON con prompts por campo personalizado',
        '{{yoastFields}}' => 'JSON con datos de Yoast SEO',
        '{{yoastFields_prompt}}' => 'JSON con prompts por campo Yoast',
        '{{gpaiSeoFields}}' => 'JSON con campos de GPAI SEO',
        '{{gpaiSeoFields_prompt}}' => 'JSON con prompts por campo GPAI SEO',
        '{{prompt}}' => 'Prompt base del usuario',
    ],
    'content_img' => [
        '{{title}}' => 'Titulo de la pagina',
        '{{customFields}}' => 'JSON con campos personalizados',
        '{{yoastFields}}' => 'JSON con datos de Yoast SEO',
        '{{gpaiSeoFields}}' => 'JSON con campos de GPAI SEO',
        '{{imageUrl}}' => 'URL de la imagen destacada',
    ],
    'template' => [
        '{{title}}' => 'Titulo de la plantilla',
        '{{globalFields}}' => 'JSON con variables globales {g{...}}',
        '{{globalFields_prompt}}' => 'JSON con prompts por variable global',
        '{{prompt}}' => 'Prompt base del usuario',
    ],
    'seo' => [
        '{{title}}' => 'Titulo de la pagina',
        '{{postContent}}' => 'Contenido de la pagina (200 palabras)',
        '{{currentSeoFields}}' => 'JSON con datos actuales de SEO',
        '{{prompt}}' => 'Prompt personalizado del usuario',
    ],
    'html' => [
        '{{htmlContent}}' => 'Codigo HTML completo de la pagina',
    ],
];
function getBasePromptDefaultForType($type)
{
    if ($type === 'seo') {
        return GPAI_SEO::getSEOBasePromptDefault();
    }
    if ($type === 'html') {
        return GPAI_SEO::getHTMLBasePromptDefault();
    }
    return GPAI_CONTENT::getBasePromptDefault($type);
}

$respond_prompts_base = null;
if ($_POST['save'] === 'prompts_base') {
    $promptsBase = [];
    foreach ($types as $type => $label) {
        $promptsBase[$type] = isset($_POST['prompts_base'][$type])
            ? wp_kses_post(wp_unslash($_POST['prompts_base'][$type]))
            : getBasePromptDefaultForType($type);
    }
    $GPAI_USE_DATA_CONFIG->setField('prompts_base', $promptsBase);

    $respond_prompts_base = [
        "status" => "ok",
        "message" => "Prompts base guardados correctamente.",
        'data' => [],
    ];
}

if ($_POST['save'] === 'reset_prompts_base') {
    $GPAI_USE_DATA_CONFIG->setField('prompts_base', []);
    $respond_prompts_base = [
        "status" => "ok",
        "message" => "Prompts base restaurados a valores predeterminados.",
        'data' => [],
    ];
}

$storedPrompts = $CONFIG['prompts_base'] ?? [];


function getContentCollapsePromptBase($type, $currentValue, $placeholders)
{
    ob_start();
?>
    <div>
        <div style="margin-bottom:12px">
            <strong>Placeholders disponibles:</strong>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px">
                <?php foreach ($placeholders[$type] as $ph => $desc):
                    echo GPAI_Tooltip(
                        ' <code style="background:#f1f3f5;padding:2px 8px;border-radius:3px;font-size:12px">' .
                            esc_html($ph) .
                            '</code>',
                        esc_attr($desc)
                    );
                endforeach; ?>
            </div>
        </div>
        <textarea
            name="prompts_base[<?= $type ?>]"
            style="width:100%;min-height:300px;font-family:monospace;font-size:12px;padding:10px"><?= esc_textarea($currentValue) ?></textarea>
    </div>
<?php
    return ob_get_clean();
}
?>
<form method="POST">
    <?= GPAI_Respond($respond_prompts_base) ?>
    <div class="notice notice-warning">
        <p>
            <strong>⚠️ Aviso:</strong> Esta secci&oacute;n es de <strong>alto nivel</strong>.
            Usuarios no experimentados <strong>no deben modificar</strong> estos prompts.
            Cambiar incorrectamente los templates puede romper la generaci&oacute;n de contenido.
        </p>
    </div>

    <input type="hidden" name="save" value="prompts_base">

    <?php
    foreach ($types as $type => $label):
        $currentValue = $storedPrompts[$type] ?? getBasePromptDefaultForType($type);
        echo GPAI_Collapse(
            esc_html($label),
            getContentCollapsePromptBase($type, $currentValue, $placeholders),
            true
        );
    endforeach;
    ?>

    <div class="content-btn" style="margin-top:16px">
        <button type="submit" class="button button-primary">Guardar Prompts Base</button>
        <button type="submit" name="save" value="reset_prompts_base" class="button">Restaurar predeterminados</button>
    </div>
</form>