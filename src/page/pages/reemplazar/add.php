<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        'Reemplazar URL / Redirect',
        'Reemplazar URL / Redirect',
        'manage_options',
        GPAI_KEY . '_reemplazar',
        'GPAI_PAGE_REEMPLAZAR_VIEW'
    );
});

function GPAI_PAGE_REEMPLAZAR_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/reemplazar/page.php';
}