<?php

use franciscoblancojn\wordpress_utils\FWUPage;

echo FWUPage::css();

$GPAI_USE_DATA_CONFIG = new GPAI_USE_DATA_CONFIG();
$GPAI_USE_DATA_DUPLICADOS = new GPAI_USE_DATA_DUPLICADOS();

$CONFIG = $GPAI_USE_DATA_CONFIG->get();

$TAGS = [
    [
        'key' => 'api_seo',
        'title' => 'API SEO',
    ],
    [
        'key' => 'api_cf',
        'title' => 'API Custom Fields',
    ],
    [
        'key' => 'api_gf',
        'title' => 'API Global Fields',
    ],
];
$defaultTag = $TAGS[0]['key'];

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>Generate Page AI — API</h1>
    <?php FWUPage::tabs($TAGS, $defaultTag); ?>
    <?php
    foreach ($TAGS as $tag) {
    ?>
        <div class="tab-content <?= $tag['key'] == $defaultTag ? 'nav-tab-active' : '' ?>" id="<?= $tag['key'] ?>">
            <?php
            require_once GPAI_DIR . 'src/page/sections/' . $tag['key'] . '.php';
            ?>
        </div>
    <?php
    }
    ?>
</div>
<?php

echo FWUPage::js(GPAI_KEY);
