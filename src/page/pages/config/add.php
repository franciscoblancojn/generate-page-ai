<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        'Configuracion',
        'Configuracion',
        'manage_options',
        GPAI_KEY . '_config',
        'GPAI_PAGE_CONFIG_VIEW'
    );
    remove_submenu_page(GPAI_KEY, GPAI_KEY);
});

function GPAI_PAGE_CONFIG_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/config/page.php';
}
