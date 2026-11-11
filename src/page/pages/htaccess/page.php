<?php

use franciscoblancojn\wordpress_utils\FWUPage;

echo FWUPage::css();

$GPAI_USE_DATA_HTACCESS = new GPAI_USE_DATA_HTACCESS();
$HTACCESS = $GPAI_USE_DATA_HTACCESS->get();

?>
<div id="page-<?= GPAI_KEY ?>" class="wrap">
    <h1>.htaccess</h1>
    <?php
    require_once GPAI_DIR . 'src/page/sections/htaccess.php';
    ?>
</div>
<?php

echo FWUPage::js(GPAI_KEY);
