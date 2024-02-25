<?php

/**
 * Plugin Name: Telegram Bot WordPress Plugin Boilerplate
 * Description: A boilerplate plugin for connecting a Telegram bot to your WordPress website.
 * Version: 	1.1.0
 * Author: 		Koorosh
 * Author URI: 	https://GitHub.com/Koorosh14/
 * Text Domain: telegram-plugin-boilerplate
 * Domain Path: /languages/
 */

use TelegramPluginBoilerplate\Core;

if (!defined('ABSPATH')) return;

define('FDTBWPB_VERSION', '1.1.0');
define('FDTBWPB_FILE', __FILE__);
define('FDTBWPB_URL', plugin_dir_url(FDTBWPB_FILE));
define('FDTBWPB_DIR', plugin_dir_path(FDTBWPB_FILE));
define('FDTBWPB_BASENAME', plugin_basename(FDTBWPB_FILE));
define('FDTBWPB_OPTIONS_KEY_DB_VERSION', 'fdtbwpb_db_version');
define('FDTBWPB_TEXT_DOMAIN', 'telegram-plugin-boilerplate');
define('FDTBWPB_SETTINGS_SLUG', 'fdtbwpb');

require('vendor/autoload.php');
require('functions.php');

function FDTBWPB()
{
	return Core::get_instance();
}
FDTBWPB();
