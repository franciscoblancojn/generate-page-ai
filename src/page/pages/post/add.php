<?php

add_action('admin_menu', function () {
    add_submenu_page(
        GPAI_KEY,
        'Post',
        'Post',
        'manage_options',
        GPAI_KEY . '_post',
        'GPAI_PAGE_POST_VIEW'
    );
});

function GPAI_PAGE_POST_VIEW()
{
    require_once GPAI_DIR . 'src/page/pages/post/page.php';
}
