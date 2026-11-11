<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        'Site Maps',
        'Site Maps',
        'manage_options',
        GPAI_KEY . '_sitemaps',
        'GPAI_PAGE_SITEMAPS_VIEW'
    );
});

function GPAI_PAGE_SITEMAPS_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/sitemaps/page.php';
}
