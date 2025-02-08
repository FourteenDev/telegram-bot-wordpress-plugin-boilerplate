<?php

namespace TelegramPluginBoilerplate;

use TelegramPluginBoilerplate\Shortcodes\PostModifiedDate;

class Shortcode
{
	public static $instance = null;

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		new PostModifiedDate();
	}
}
