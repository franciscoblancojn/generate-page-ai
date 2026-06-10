<?php

require_once GPAI_DIR . 'src/data/_.php';
require_once GPAI_DIR . 'src/ai/_.php';
require_once GPAI_DIR . 'src/hook/_.php';
require_once GPAI_DIR . 'src/api/_.php';
require_once GPAI_DIR . 'src/templates/_.php';
require_once GPAI_DIR . 'src/meta-box/gpai-seo.php';
require_once GPAI_DIR . 'src/meta-box/gpai-parent.php';
require_once GPAI_DIR . 'src/frontend/gpai-seo-output.php';
require_once GPAI_DIR . 'src/page/_.php';


if (defined('ELEMENTOR_VERSION')) {
    require_once GPAI_DIR . 'src/elementor/_.php';
}