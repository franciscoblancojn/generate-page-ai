<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        '.htaccess',
        '.htaccess',
        'manage_options',
        GPAI_KEY . '_htaccess',
        'GPAI_PAGE_HTACCESS_VIEW'
    );
});

function GPAI_PAGE_HTACCESS_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/htaccess/page.php';
}
