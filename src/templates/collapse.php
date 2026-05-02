<?php
function DPAI_Collapse($title, $content, $open = false)
{
    ob_start();
?>
    <details <?= $open ? "open":"" ?>>
        <summary style="display: flex;">
            <span><?= $title ?> </span>
        </summary>
        <div>
            <?= $content ?>
        </div>
    </details>
<?php
    return ob_get_clean();
}
