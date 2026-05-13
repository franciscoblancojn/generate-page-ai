<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        'Plantillas',
        'Plantillas',
        'manage_options',
        GPAI_KEY . '_plantilllas',
        'GPAI_PAGE_PLANTILLAS_VIEW'
    );
});

function GPAI_PAGE_PLANTILLAS_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/plantillas/page.php';
}
