<?php

namespace TelegramPluginBoilerplate\Shortcodes;

class ShortcodeManager
{
	public function __construct()
	{
		$this->registerAllShortcodes();
	}

	/**
	 * Registers (calls `add_shortcode`) all shortcodes for every file in the `src/Shortcodes/` directory that ends with `Shortcode.php`.
	 *
	 * @return	void
	 */
	private function registerAllShortcodes()
	{
		foreach (glob(FDTBWPB_DIR . '/src/Shortcodes/*Shortcode.php') as $file)
		{
			$class = '\\' . __NAMESPACE__ . '\\' . basename($file, '.php');

			if (class_exists($class) && !empty($class::$tag))
				add_shortcode($class::$tag, [new $class(), 'run']);
		}
	}
}
