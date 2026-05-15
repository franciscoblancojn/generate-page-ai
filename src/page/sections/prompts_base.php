<?php

$types = [
    'content' => 'Generar contenido (getPrompt)',
    // 'content_img' => 'Generar imagen (getPromptImg)', //de momento no esta disponible
    'template' => 'Variables globales (getContentTemplate)',
];

$placeholders = [
    'content' => [
        '{{title}}' => 'Titulo de la pagina',
        '{{customFields}}' => 'JSON con campos personalizados',
        '{{customFields_prompt}}' => 'JSON con prompts por campo personalizado',
        '{{yoastFields}}' => 'JSON con datos de Yoast SEO',
        '{{yoastFields_prompt}}' => 'JSON con prompts por campo Yoast',
        '{{prompt}}' => 'Prompt base del usuario',
    ],
    'content_img' => [
        '{{title}}' => 'Titulo de la pagina',
        '{{customFields}}' => 'JSON con campos personalizados',
        '{{yoastFields}}' => 'JSON con datos de Yoast SEO',
        '{{imageUrl}}' => 'URL de la imagen destacada',
    ],
    'template' => [
        '{{title}}' => 'Titulo de la plantilla',
        '{{globalFields}}' => 'JSON con variables globales {g{...}}',
        '{{globalFields_prompt}}' => 'JSON con prompts por variable global',
        '{{prompt}}' => 'Prompt base del usuario',
    ],
];
$respond_prompts_base = null;
if ($_POST['save'] === 'prompts_base') {
    $promptsBase = [];
    foreach ($types as $type => $label) {
        $promptsBase[$type] = isset($_POST['prompts_base'][$type])
            ? wp_kses_post(wp_unslash($_POST['prompts_base'][$type]))
            : GPAI_CONTENT::getBasePromptDefault($type);
    }
    $GPAI_USE_DATA_CONFIG->setField('prompts_base', $promptsBase);

    $respond_prompts_base = [
        "status" => "ok",
        "message" => "Prompts base guardados correctamente.",
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
        <div style="margin-top:6px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <button
                type="button"
                class="button gpai-restore-prompt"
                data-type="<?= $type ?>">Restaurar predeterminado</button>
        </div>
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
        $currentValue = $storedPrompts[$type] ?? GPAI_CONTENT::getBasePromptDefault($type);
        echo GPAI_Collapse(
            esc_html($label),
            getContentCollapsePromptBase($type, $currentValue, $placeholders),
            true
        );
    endforeach;
    ?>

    <div class="content-btn" style="margin-top:16px">
        <button type="submit" class="button button-primary">Guardar Prompts Base</button>
    </div>
</form>

<script>
    var gpaiDefaults = <?= json_encode([
                            'content' => GPAI_CONTENT::getBasePromptDefault('content'),
                            'content_img' => GPAI_CONTENT::getBasePromptDefault('content_img'),
                            'template' => GPAI_CONTENT::getBasePromptDefault('template'),
                        ]) ?>;

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('gpai-restore-prompt')) {
            var type = e.target.getAttribute('data-type');
            var textarea = e.target.closest('details').querySelector('textarea');
            if (textarea && gpaiDefaults[type] !== undefined) {
                textarea.value = gpaiDefaults[type];
            }
        }
    });
</script>