<?php

namespace TelegramPluginBoilerplate;

class API
{
	protected Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->instantiateAllEndpoints();
	}

	/**
	 * Registers (calls `__construct` on) every file in the `src/Api/Endpoints/` directory and all of its subfolders.
	 *
	 * @return	void
	 */
	private function instantiateAllEndpoints(): void
	{
		$baseDir = FDTBWPB_DIR . '/src/Api/Endpoints';
		foreach (fdtbwpbGlobRecursive("$baseDir**/*.php") as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\Api\\Endpoints\\' . fdtbwpbConvertFilePathToRelativeNamespace($file, $baseDir);
			if (class_exists($class))
				$this->container->make($class);
		}
	}
}
