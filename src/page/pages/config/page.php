<?php
require_once GPAI_DIR . 'src/css/global.php';

$GPAI_USE_DATA_CONFIG = new GPAI_USE_DATA_CONFIG();
$GPAI_USE_DATA_DUPLICADOS = new GPAI_USE_DATA_DUPLICADOS();

$CONFIG = $GPAI_USE_DATA_CONFIG->get();

$TAGS = [
    [
        'key' => 'config',
        'title' => 'Configuracion IA',
    ],
    [
        'key' => 'prompts_base',
        'title' => 'Prompts Base',
    ],
    ...(GPAI_MODE_DEV ? [
        [
            'key' => 'test',
            'title' => 'Pruebas',
        ]
    ] : [])
];
$defaultTag =  $TAGS[0]['key'];

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>Generate Page AI</h1>
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
