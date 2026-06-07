<?php

use franciscoblancojn\wordpress_utils\FWUPage;

echo FWUPage::css();

$GPAI_USE_DATA_SITEMAPS = new GPAI_USE_DATA_SITEMAPS();
$SITEMAPS = $GPAI_USE_DATA_SITEMAPS->getSitemaps();

$TAGS = [
    [
        'key' => 'sitemaps',
        'title' => 'Site Maps',
    ],
    [
        'key' => 'crear_sitemap',
        'title' => 'Crear Site Map',
    ],
    [
        'key' => 'config-sitemaps',
        'title' => 'Configuraciones',
    ],
];

$defaultTag = $TAGS[0]['key'];

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>Site Maps</h1>
    <?php FWUPage::tabs($TAGS, $defaultTag); ?>
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

echo FWUPage::js(GPAI_KEY);
