<?php

namespace TelegramPluginBoilerplate;

use TelegramPluginBoilerplate\Services\Posts\Post;

class Service
{
	public static $instance = null;

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		// new Post();
	}
}
