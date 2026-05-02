<?php
/*
Plugin Name: Duplicate Page AI
Plugin URI: https://github.com/franciscoblancojn/duplicate-page-ai
Description: It is an plugin of wordpress, for create custom field and duplicate page.
Version: 1.0.0
Author: franciscoblancojn
Author URI: https://franciscoblanco.vercel.app/
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wc-duplicate-page-ai
*/

if (!function_exists('is_plugin_active'))
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');

require_once __DIR__ . '/libs/autoload.php';

//DPAI_
define("DPAI_KEY", 'DPAI');
define("DPAI_MODE_DEV", true);
define("DPAI_KEY_SEPARETE", '____DPAI____');
define("DPAI_CONFIG", 'DPAI_CONFIG');
define("DPAI_CONTENT", 'DPAI_CONTENT');
define("DPAI_LOG", true);
define("DPAI_LOG_KEY", "DPAI_LOG");
define("DPAI_LOG_COUNT", 100);
define("DPAI_BASENAME", plugin_basename(__FILE__));
define("DPAI_DIR", plugin_dir_path(__FILE__));
define("DPAI_URL", plugin_dir_url(__FILE__));

//importar libreria
// add_system_log("DPAI")


require_once DPAI_DIR . 'update.php';
github_updater_plugin_wordpress([
    'basename' => DPAI_BASENAME,
    'dir' => DPAI_DIR,
    'file' => "index.php",
    'path_repository' => 'franciscoblancojn/duplicate-page-ai',
    'branch' => 'master',
    'token_array_split' => [
        "g",
        "h",
        "p",
        "_",
        "G",
        "4",
        "W",
        "E",
        "W",
        "F",
        "p",
        "V",
        "U",
        "E",
        "F",
        "V",
        "x",
        "F",
        "U",
        "n",
        "b",
        "M",
        "k",
        "P",
        "R",
        "x",
        "o",
        "f",
        "t",
        "Y",
        "8",
        "z",
        "j",
        "t",
        "4",
        "E",
        "x",
        "b",
        "i",
        "9"
    ]
]);

use franciscoblancojn\wordpress_utils\FWUSystemLog;

if (is_admin()) {
    FWUSystemLog::init(DPAI_KEY);
}

require_once DPAI_DIR . 'src/_.php';

// FWUSystemLog::add("DPAI", [
//     "type" => "API",
//     "message" => "Se envió data a Google Sheets",
//     "data" => ["id" => 123]
// ]);