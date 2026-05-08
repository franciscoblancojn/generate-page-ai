<?php
function GPAI_Collapse($title, $content, $open = false)
{
    ob_start();
?>
    <details <?= $open ? "open":"" ?>>
        <summary style="display: flex;">
            <?= $title ?>
        </summary>
        <div>
            <?= $content ?>
        </div>
    </details>
<?php
    return ob_get_clean();
}
