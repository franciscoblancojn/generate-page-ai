<?php

require_once GPAI_DIR . 'src/api/cf.php';
require_once GPAI_DIR . 'src/api/yoast.php';
require_once GPAI_DIR . 'src/api/gpai_seo.php';
require_once GPAI_DIR . 'src/api/export_import.php';
require_once GPAI_DIR . 'src/api/sitemaps.php';
require_once GPAI_DIR . 'src/api/imagenes.php';
require_once GPAI_DIR . 'src/api/analisis.php';
require_once GPAI_DIR . 'src/api/seo_api.php';
require_once GPAI_DIR . 'src/api/cf_api.php';

GPAI_API_SEO::init();
GPAI_API_CF::init();