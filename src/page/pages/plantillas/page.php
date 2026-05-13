<?php
require_once GPAI_DIR . 'src/css/global.php';


$GPAI_USE_DATA_TEMPLATES = new GPAI_USE_DATA_TEMPLATES();
$GPAI_USE_DATA_TEMPLATES_CONTENT = new GPAI_USE_DATA_TEMPLATES_CONTENT();

$TEMPLATE_CONFIG = $GPAI_USE_DATA_TEMPLATES->get();

$TAGS = [
    [
        'key' => 'plantillas',
        'title' => 'Gestionar Plantilla',
    ],
    [
        'key' => 'procesar_plantillas',
        'title' => 'Procesar Plantilla',
    ],
];

$defaultTag =  $TAGS[0]['key'];

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>Plantillas Globales</h1>
    <div class="nav-tab-wrapper woo-nav-tab-wrapper">
        <?php
        foreach ($TAGS  as $key => $value) {
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
    foreach ($TAGS  as $key => $value) {
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