<?php

use franciscoblancojn\wordpress_utils\FWUPage;

echo FWUPage::css();

$GPAI_USE_DATA_HTACCESS = new GPAI_USE_DATA_HTACCESS();
$HTACCESS = $GPAI_USE_DATA_HTACCESS->get();

$TAGS = [
    [
        'key' => 'reemplazar',
        'title' => 'Reemplazar URL',
    ],
    [
        'key' => 'redirect',
        'title' => 'Redirect',
    ],
];

$defaultTag = $TAGS[0]['key'];

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>Reemplazar URL / Redirect</h1>
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