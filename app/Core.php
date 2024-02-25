<?php namespace TelegramPluginBoilerplate;

class Core
{
	public static $instance = null;

	private $options;

	public static function get_instance()
	{
		null === self::$instance && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		$this->global_classes();
		if (is_admin()) $this->admin_classes();
		else $this->frontend_classes();
	}

	public function global_classes()
	{
		Model::get_instance();
		API::get_instance();
	}

	public function admin_classes()
	{
		Settings::get_instance();
	}

	public function frontend_classes() {}

	public function plugin_url($path = null)
	{
		return untrailingslashit(FDTBWPB_URL . $path);
	}

	public function plugin_dir($path = null)
	{
		return untrailingslashit(FDTBWPB_DIR . $path);
	}

	/**
	 * Returns a plugin view.
	 *
	 * @param	string		$file_path		Separate path parts with dots (`.`).
	 * @param	array		$passed_array
	 * @param	bool		$echo			Echo/print the view or just return the section/view.
	 *
	 * @return	mixed
	 */
	public function view($file_path, $passed_array = [], $echo = true)
	{
		if ($echo) echo Views::get_instance()->section($file_path, $passed_array);
		else return Views::get_instance()->section($file_path, $passed_array);
	}

	/**
	 * Returns `Model` class.
	 *
	 * @return	Model
	 */
	public function model()
	{
		return Model::get_instance();
	}

	/**
	 * Returns `Helper` class.
	 *
	 * @return	Helper
	 */
	public function helper()
	{
		return Helper::get_instance();
	}

	/**
	 * Returns a plugin option.
	 *
	 * @param	string		$option_name
	 *
	 * @return	mixed|null
	 */
	public function option($option_name)
	{
		if (empty($this->options)) $this->options = get_option('fdtbwpb_options');

		if (isset($this->options[$option_name]) && !empty($this->options[$option_name]))
			return $this->options[$option_name];

		return null;
	}
}
