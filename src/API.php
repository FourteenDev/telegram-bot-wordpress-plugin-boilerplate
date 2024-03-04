<?php

namespace TelegramPluginBoilerplate;

class API
{
	public static $instance = null;

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		$this->instantiateAllEndpoints();
	}

	/**
	 * Calls the `getInstance()` method on every file in the `app/API/Endpoints/` directory.
	 *
	 * @return	void
	 */
	private function instantiateAllEndpoints()
	{
		foreach (glob(FDTBWPB_DIR . '/app/API/Endpoints/*.php') as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\API\\Endpoints\\' . basename($file, '.php');

			if (class_exists($class)) $class::getInstance();
		}
	}
}
