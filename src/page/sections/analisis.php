<?php

$post_id = $CONFIG['post_id'] ?? null;

if (isset($_POST['save']) && $_POST['save'] == 'gpai_analisis') {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : $post_id;
}

?>
<div id="gpai-analisis-section">
    <?php if (!$post_id) { ?>
        <p style="padding:1rem 0;">Selecciona un post en la pesta&ntilde;a "Post, Campos y Prompts" primero.</p>
    <?php } else { ?>
        <?= GPAI_Analisis_Post($post_id) ?>
    <?php } ?>
</div>
