<?php
require_once GPAI_DIR . 'src/css/global.php';

$GPAI_USE_DATA_HTACCESS = new GPAI_USE_DATA_HTACCESS();
$HTACCESS = $GPAI_USE_DATA_HTACCESS->get();

$TAGS = [
    [
        'key' => 'htaccess',
        'title' => '.htaccess',
    ],
];

$defaultTag = $TAGS[0]['key'];

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>.htaccess</h1>
    <?php
    require_once GPAI_DIR . 'src/page/sections/htaccess.php';
    ?>
</div>
<?php

require_once GPAI_DIR . 'src/js/global.php';
