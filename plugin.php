<?php

/**
 * Plugin Name: Boilerplate Telegram Bot Plugin
 * Description: A boilerplate plugin for connecting a Telegram bot to your WordPress website.
 * Version: 	1.1.0
 * Author: 		Koorosh
 * Author URI: 	https://GitHub.com/Koorosh14/
 * Text Domain: boilerplate-telegram-plugin
 * Domain Path: /languages/
 */

use BoilerplateTelegramPlugin\Core;

if (!defined('ABSPATH')) return;

define('BTBP_VERSION', '1.1.0');
define('BTBP_FILE', __FILE__);
define('BTBP_URL', plugin_dir_url(BTBP_FILE));
define('BTBP_DIR', plugin_dir_path(BTBP_FILE));
define('BTBP_BASENAME', plugin_basename(BTBP_FILE));
define('BTBP_OPTIONS_KEY_DB_VERSION', 'btbp_db_version');
define('BTBP_TEXT_DOMAIN', 'boilerplate-telegram-plugin');
define('BTBP_SETTINGS_SLUG', 'btbp');

require('vendor/autoload.php');
require('functions.php');

function BTBP()
{
	return Core::get_instance();
}
BTBP();
