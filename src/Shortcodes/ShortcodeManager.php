<?php

namespace TelegramPluginBoilerplate\Shortcodes;

use TelegramPluginBoilerplate\Container;

/**
 * Manages registration of shortcodes.
 */
class ShortcodeManager
{
	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;

		$this->registerAllShortcodes();
	}

	/**
	 * Registers (calls `add_shortcode`) all shortcodes for every file in the `src/Shortcodes/` directory that ends with `Shortcode.php`.
	 *
	 * @return	void
	 */
	private function registerAllShortcodes(): void
	{
		foreach (glob(FDTBWPB_DIR . '/src/Shortcodes/*Shortcode.php') as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\' . basename($file, '.php');

			if (class_exists($class) && !empty($class::$tag))
				add_shortcode($class::$tag, [$this->container->make($class), 'run']);
		}
	}
}
