<?php

namespace TelegramPluginBoilerplate;

class API
{
	public static $instance = null;

	public static function get_instance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		$this->instantiate_all_endpoints();
	}

	/**
	 * Calls the `get_instance()` method on every file in the `app/API/Endpoints/` directory.
	 *
	 * @return	void
	 */
	private function instantiate_all_endpoints()
	{
		foreach (glob(FDTBWPB_DIR . '/app/API/Endpoints/*.php') as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\API\\Endpoints\\' . basename($file, '.php');

			if (class_exists($class)) $class::get_instance();
		}
	}
}
