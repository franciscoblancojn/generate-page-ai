<?php
function DPAI_Tooltip($title, $text)
{
    ob_start();
?>
    <div>
        <?= $title ?>
        <span class="goshap-tooltip">
            <span class="dashicons dashicons-info"></span>
            <span class="goshap-tooltip-text"><?= $text  ?></span>
        </span>
    </div>
<?php
    return ob_get_clean();
}
