<?php
function parseRespondMessage($text)
{
    return preg_replace(
        '/(https?:\/\/[^\s]+)/',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
        $text
    );
}

function GPAI_Respond($respond)
{
    if (!isset($respond)) {
        return "";
    }

    ob_start();
?>
    <p class="message <?= $respond['status'] ?>" data="<?= json_encode($respond['data']) ?>">
        <?= (isset($respond['data']['post_id']) ? get_the_title($respond['data']['post_id']) . " => " : ''); ?>
        <?= (isset($respond['data']['title']) ? ($respond['data']['title']) . " => " : ''); ?>
        <?= parseRespondMessage($respond['message']); ?>
        <?php
        if ($respond['status'] == "ok") {
            if (isset($respond['data']['url'])) {
                $btn_label = strpos($respond['data']['url'], 'action=elementor') !== false ? 'Ver Plantilla' : 'Ver Pagina';
        ?>
                <a href="<?php echo esc_url($respond['data']['url']); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary btn-to-right">
                    <?= $btn_label ?>
                </a>
        <?php
            }
        }
        ?>
    </p>
<?php
    return ob_get_clean();
}
