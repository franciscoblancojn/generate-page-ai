<?php

// use franciscoblancojn\wordpress_utils\FWUSystemLog;
// 1. Crear menú en el admin
add_action('admin_menu', function () {
    add_menu_page(
        'Generate Page AI Configuración', // Título página
        'Generate Page AI',              // Nombre en menú
        'manage_options',        // Permisos
        GPAI_KEY,      // Slug
        'GPAI_REDIRECT_FIRST_SUBMENU',
        'dashicons-admin-site'
    );
});
function GPAI_REDIRECT_FIRST_SUBMENU()
{
    wp_redirect(admin_url('admin.php?page=' . GPAI_KEY . '_config'));
    exit;
}
// ELIMINAR SUBMENU DUPLICADO AUTOMÁTICO 