<?php

namespace TelegramPluginBoilerplate;

class Core
{
	public static $instance = null;

	private $options;

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		$this->globalClasses();
		if (is_admin()) $this->adminClasses();
		else $this->frontendClasses();
	}

	public function globalClasses()
	{
		Model::getInstance();
		API::getInstance();
	}

	public function adminClasses()
	{
		Settings::getInstance();
	}

	public function frontendClasses() {}

	/**
	 * Returns plugin's URL path, without any slashes in the end (e.g. `https://Site.com/wp-content/plugins/my-plugin`).
	 *
	 * @param	string	$path	Path to append to the end of the URL, without any slashes in the beginning (e.g. `path/to/my-file.php`).
	 *
	 * @return	string
	 */
	public function url($path = '')
	{
		return untrailingslashit(FDTBWPB_URL . $path);
	}

	/**
	 * Returns plugin's dir path, without any slashes in the end (e.g. `/var/www/html/wp-content/plugins/my-plugin`).
	 *
	 * @param	string	$path	Path to append to the end of the dir, without any slashes in the beginning (e.g. `path/to/my-file.php`).
	 *
	 * @return	string
	 */
	public function dir($path = '')
	{
		return untrailingslashit(FDTBWPB_DIR . $path);
	}

	/**
	 * Returns a plugin view.
	 *
	 * @param	string		$filePath		Separate path parts with dots (`.`).
	 * @param	array		$passedArray
	 * @param	bool		$echo			Echo/print the view or just return the section/view.
	 *
	 * @return	mixed
	 */
	public function view($filePath, $passedArray = [], $echo = true)
	{
		if (!$echo) return View::getInstance()->display($filePath, $passedArray);

		echo View::getInstance()->display($filePath, $passedArray);
	}

	/**
	 * Returns `Model` class.
	 *
	 * @return	Model
	 */
	public function model()
	{
		return Model::getInstance();
	}

	/**
	 * Returns `Helper` class.
	 *
	 * @return	Helper
	 */
	public function helper()
	{
		return Helper::getInstance();
	}

	/**
	 * Returns a plugin option.
	 *
	 * @param	string		$optionName
	 *
	 * @return	mixed|null
	 */
	public function option($optionName)
	{
		if (empty($this->options)) $this->options = get_option('fdtbwpb_options');

		if (isset($this->options[$optionName]) && !empty($this->options[$optionName]))
			return $this->options[$optionName];

		return null;
	}
}
