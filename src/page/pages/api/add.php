<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        'API',
        'API',
        'manage_options',
        GPAI_KEY . '_api',
        'GPAI_PAGE_API_VIEW'
    );
});

function GPAI_PAGE_API_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/api/page.php';
}
