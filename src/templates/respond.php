<?php
function GPAI_Respond($respond)
{
    if (!isset($respond)) {
        return "";
    }

    function parseRespondMessage($text)
    {
        return preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $text
        );
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
        ?>
                <a href="<?php echo esc_url($respond['data']['url']); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary btn-to-right">
                    Ver Pagina
                </a>
        <?php
            }
        }
        ?>
    </p>
<?php
    return ob_get_clean();
}
