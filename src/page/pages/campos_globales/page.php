<?php
require_once GPAI_DIR . 'src/css/global.php';

$TAGS = [
    [
        'key' => 'campos_globales',
        'title' => 'Campos Globales',
    ],
];

$defaultTag = $TAGS[0]['key'];

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>Campos Globales</h1>
    <div class="nav-tab-wrapper woo-nav-tab-wrapper">
        <?php
        foreach ($TAGS as $key => $value) {
        ?>
            <a
                class="nav-tab <?= $value['key'] == $defaultTag ? "nav-tab-active" : "" ?>"
                data-tab="<?= $value['key'] ?>"
                href="#tag-<?= $value['key'] ?>">
                <?= $value['title'] ?>
            </a>
        <?php
        }
        ?>
    </div>
    <?php
    foreach ($TAGS as $key => $value) {
    ?>
        <div class="tab-content <?= $value['key'] == $defaultTag ? "nav-tab-active" : "" ?>" id="<?= $value['key'] ?>">
            <?php
            require_once GPAI_DIR . 'src/page/sections/' . $value['key'] . ".php";
            ?>
        </div>
    <?php
    }
    ?>
</div>
<?php

require_once GPAI_DIR . 'src/js/global.php';
