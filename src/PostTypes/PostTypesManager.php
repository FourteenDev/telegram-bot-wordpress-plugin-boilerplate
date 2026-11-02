<?php

namespace TelegramPluginBoilerplate\PostTypes;

use TelegramPluginBoilerplate\Container;

/**
 * Manages registration of post types.
 */
class PostTypesManager
{
	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;

		$this->registerAllPostTypes();
	}

	/**
	 * Registers (calls `__construct` on) every file in the `src/PostTypes/` directory and all of its subfolders that ends with `PostType.php`.
	 *
	 * @return	void
	 */
	private function registerAllPostTypes(): void
	{
		$baseDir = FDTBWPB_DIR . '/src/PostTypes';
		foreach (fdtbwpbGlobRecursive("$baseDir**/*PostType.php") as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\' . fdtbwpbConvertFilePathToRelativeNamespace($file, $baseDir);
			if (class_exists($class))
				$this->container->make($class);
		}
	}
}
