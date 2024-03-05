<?php

namespace TelegramPluginBoilerplate;

class View
{
	public static $instance = null;

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct() {}

	/**
	 * Requires/Executes the given file from `views/` folder.
	 *
	 * @param	string	$file	File path without `.php` extension, where path parts are separated with dots (e.g. `path.to.file`).
	 * @param	array	$data	Input data to pass to the file. The array will be extracted into multiple variables (e.g. `['var1' => 'Foo', 'var2' => 'Bar']`).
	 *
	 * @return	void
	 */
	public function require($file, $data = [])
	{
		$file = rtrim($file, '.php');
		$file = str_replace('.', DIRECTORY_SEPARATOR, trim($file));
		$file = FDTBWPB_DIR . 'views' . DIRECTORY_SEPARATOR . $file . '.php';
		if (!file_exists($file)) return '';

		extract($data);
		require $file;
	}

	/**
	 * Outputs the given file from `views/` folder into buffer.
	 *
	 * @param	string			$file	File path without `.php` extension, where path parts are separated with dots (e.g. `path.to.file`).
	 * @param	array			$data	Input data to pass to the file. The array will be extracted into multiple variables (e.g. `['var1' => 'Foo', 'var2' => 'Bar']`).
	 *
	 * @return	string|false
	 */
	public function display($file, $data = [])
	{
		ob_start();
		$this->require($file, $data);
		return ob_get_clean();
	}
}
