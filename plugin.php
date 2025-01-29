<?php

/**
 * Plugin Name: Telegram Bot WordPress Plugin Boilerplate
 * Plugin URI:  https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate
 * Description: A boilerplate plugin for connecting a Telegram bot to your WordPress website.
 * Version: 	1.2.1
 * Author:      Fourteen Development
 * Author URI:  https://Fourteen.dev/
 * License:     MIT
 * License URI: https://opensource.org/license/mit/
 * Text Domain: telegram-plugin-boilerplate
 * Domain Path: /languages
 */

use TelegramPluginBoilerplate\Core;

if (!defined('ABSPATH')) return;

define('FDTBWPB_VERSION', '1.2.1');
define('FDTBWPB_FILE', __FILE__);
define('FDTBWPB_URL', plugin_dir_url(FDTBWPB_FILE));
define('FDTBWPB_DIR', plugin_dir_path(FDTBWPB_FILE));
define('FDTBWPB_BASENAME', plugin_basename(FDTBWPB_FILE));
define('FDTBWPB_SETTINGS_SLUG', 'fdtbwpb');
define('FDTBWPB_OPTIONS_KEY_DB_VERSION', 'fdtbwpb_db_version');

require_once 'vendor/autoload.php';
require_once 'functions.php';

function FDTBWPB()
{
	return Core::getInstance();
}
FDTBWPB();
