<?php

add_action('admin_menu', function () {
    if (!(
        is_plugin_active('static-page/index.php') ||
        is_plugin_active('static-page-master/index.php')
    )) return;

    add_submenu_page(
        GPAI_KEY,
        'Optimización HTML',
        'Optimización HTML',
        'manage_options',
        GPAI_KEY . '_html',
        'GPAI_PAGE_HTML_VIEW'
    );
});

function GPAI_PAGE_HTML_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/html/page.php';
}
