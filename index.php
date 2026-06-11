<?php
/*
Plugin Name: Generate Page AI
Plugin URI: https://github.com/franciscoblancojn/generate-page-ai
Description: It is an plugin of wordpress, for create custom field and duplicate page.
Version: 1.17.0
Author: franciscoblancojn
Author URI: https://franciscoblanco.vercel.app/
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wc-generate-page-ai
*/

if (!function_exists('is_plugin_active'))
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');

require_once __DIR__ . '/libs/autoload.php';

//GPAI_
define("GPAI_KEY", 'GPAI');
define("GPAI_MODE_DEV", in_array($_SERVER['HTTP_HOST'] ?? '', ['wordpress.local', 'localhost', '127.0.0.1',]));
define("GPAI_KEY_SEPARETE", '____GPAI____');
define("GPAI_CONFIG", 'GPAI_CONFIG');
define("GPAI_CONTENT", 'GPAI_CONTENT');
define("GPAI_GENERACION_PAGINAS_CON_CONTENT_INDEPENDIENTE", GPAI_KEY . '_GENERACION_PAGINAS_CON_CONTENT_INDEPENDIENTE');
define("GPAI_CONTENT_INDEPENDIENTE_META", GPAI_KEY . '_CONTENT_INDEPENDIENTE');
define("GPAI_LOG", true);
define("GPAI_LOG_KEY", "GPAI_LOG");
define("GPAI_LOG_COUNT", 100);
define("GPAI_BASENAME", plugin_basename(__FILE__));
define("GPAI_DIR", plugin_dir_path(__FILE__));
define("GPAI_URL", plugin_dir_url(__FILE__));

function GPAI_get_version()
{
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}
use franciscoblancojn\wordpress_utils\FWUUpdate;

FWUUpdate::init([
    'basename' => GPAI_BASENAME,
    'dir' => GPAI_DIR,
    'file' => "index.php",
    'path_repository' => 'franciscoblancojn/generate-page-ai',
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
    FWUSystemLog::init(GPAI_KEY);
}

if (
    !is_plugin_active('duplicate-post/duplicate-post.php')
) {
    function GPAI_Error_Install_o_Active()
    {
?>
        <div class="notice notice-error is-dismissible">
            <p>
                Generate Page AI requiere el plugin "Yoast Duplicate Post" para funcionar correctamente.
            </p>
        </div>
<?php
    }
    add_action('admin_notices', 'GPAI_Error_Install_o_Active');
} else {
    require_once GPAI_DIR . 'src/_.php';
}
