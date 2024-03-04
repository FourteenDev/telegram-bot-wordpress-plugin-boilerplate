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

	public function url($path = null)
	{
		return untrailingslashit(FDTBWPB_URL . $path);
	}

	public function dir($path = null)
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
		if ($echo) echo Views::getInstance()->section($filePath, $passedArray);
		else return Views::getInstance()->section($filePath, $passedArray);
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
