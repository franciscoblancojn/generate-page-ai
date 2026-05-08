<?php

// use franciscoblancojn\wordpress_utils\FWUSystemLog;
// 1. Crear menú en el admin
add_action('admin_menu', function () {
    add_menu_page(
        'Generate Page AI Configuración', // Título página
        'Generate Page AI',              // Nombre en menú
        'manage_options',        // Permisos
        GPAI_KEY,      // Slug
        'GPAI_PAGE_VIEW'  // Callback
    );
});

// 2. Página HTML
function GPAI_PAGE_VIEW()
{
    require_once GPAI_DIR . 'src/page/page.php';
}
