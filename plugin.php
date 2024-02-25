<?php

/**
 * Plugin Name: Telegram Bot WordPress Plugin Boilerplate
 * Plugin URI:  https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate
 * Description: A boilerplate plugin for connecting a Telegram bot to your WordPress website.
 * Version: 	1.1.0
 * Author:      Fourteen Development
 * Author URI:  https://Fourteen.dev/
 * License:     MIT
 * License URI: https://opensource.org/license/mit/
 * Text Domain: telegram-plugin-boilerplate
 * Domain Path: /languages
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
