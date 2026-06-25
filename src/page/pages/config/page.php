<?php

use franciscoblancojn\wordpress_utils\FWUPage;

echo FWUPage::css();

$GPAI_USE_DATA_CONFIG = new GPAI_USE_DATA_CONFIG();
$GPAI_USE_DATA_DUPLICADOS = new GPAI_USE_DATA_DUPLICADOS();

$CONFIG = $GPAI_USE_DATA_CONFIG->get();

$TAGS = [
    [
        'key' => 'config',
        'title' => 'Configuracion IA',
    ],
    [
        'key' => 'api_seo',
        'title' => 'API SEO',
    ],
    [
        'key' => 'api_cf',
        'title' => 'API Custom Fields',
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
    <?php FWUPage::tabs($TAGS, $defaultTag); ?>
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

echo FWUPage::js(GPAI_KEY);
