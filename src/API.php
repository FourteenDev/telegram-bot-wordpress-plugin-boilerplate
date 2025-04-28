<?php

namespace TelegramPluginBoilerplate;

class API
{
	protected $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->instantiateAllEndpoints();
	}

	/**
	 * Calls the `getInstance()` method on every file in the `src/API/Endpoints/` directory.
	 *
	 * @return	void
	 */
	private function instantiateAllEndpoints()
	{
		foreach (glob(FDTBWPB_DIR . '/src/API/Endpoints/*.php') as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\API\\Endpoints\\' . basename($file, '.php');

			if (class_exists($class))
				$this->container->make($class);
		}
	}
}
