<?php

$post_id = $CONFIG['post_id'] ?? null;

if (isset($_POST['save']) && $_POST['save'] == 'gpai_imagenes') {
    $post_id = $_POST['post_id'] ?? $post_id;
}

?>
<div id="gpai-imagenes-section">
    <?php if (!$post_id) { ?>
        <p style="padding:1rem 0;">Selecciona un post en la pestaña "Post, Campos y Prompts" primero.</p>
    <?php } else { ?>
        <?= GPAI_Imagenes_Post($post_id) ?>
    <?php } ?>
</div>
