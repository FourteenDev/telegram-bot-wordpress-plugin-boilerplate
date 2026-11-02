<?php

namespace TelegramPluginBoilerplate\Shortcodes;

use TelegramPluginBoilerplate\Container;

/**
 * Manages registration of shortcodes.
 */
class ShortcodesManager
{
	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;

		$this->registerAllShortcodes();
	}

	/**
	 * Registers (calls `add_shortcode` on) all shortcodes for every file in the `src/Shortcodes/` directory and all of its subfolders that ends with `Shortcode.php`.
	 *
	 * @return	void
	 */
	private function registerAllShortcodes(): void
	{
		$baseDir = FDTBWPB_DIR . '/src/Shortcodes';
		foreach (fdtbwpbGlobRecursive("$baseDir**/*Shortcode.php") as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\' . fdtbwpbConvertFilePathToRelativeNamespace($file, $baseDir);
			if (class_exists($class) && !empty($class::$tag))
				add_shortcode($class::$tag, [$this->container->make($class), 'run']);
		}
	}
}
