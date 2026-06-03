<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        'Campos Globales',
        'Campos Globales',
        'manage_options',
        GPAI_KEY . '_campos_globales',
        'GPAI_PAGE_CAMPOS_GLOBALES_VIEW'
    );
});

function GPAI_PAGE_CAMPOS_GLOBALES_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/campos_globales/page.php';
}
