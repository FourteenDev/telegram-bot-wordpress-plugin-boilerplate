<?php

namespace TelegramPluginBoilerplate;

use TelegramPluginBoilerplate\Services\Posts\Post;
use TelegramPluginBoilerplate\Services\Integrations\Elementor;
use TelegramPluginBoilerplate\Services\Integrations\RankMath;

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
		// Posts
		// new Post();

		// Integrations
		// new Elementor();
		// new RankMath();
	}
}
