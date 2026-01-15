<?php

namespace TelegramPluginBoilerplate;

use TelegramPluginBoilerplate\Shortcodes\ShortcodeManager;

class Core
{
	protected $container;

	private $options;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->container->singleton(Core::class, function () { return $this; });

		$this->registerDependencies();

		// Initialize dependencies through container
		$this->container->make(Model::class);
		$this->container->make(Asset::class);
		$this->container->make(API::class);

		if (is_admin())
		{
			$this->container->make(Menu::class);
		} else {
			$this->container->make(ShortcodeManager::class);
		}

		$this->container->make(Service::class);

		add_action('plugins_loaded', [$this, 'i18n']);
	}

	/**
	 * Registers all the dependencies needed for the plugin.
	 *
	 * @return	void
	 */
	private function registerDependencies()
	{
		$this->container->singleton(Model::class);
		$this->container->singleton(Asset::class);
		$this->container->singleton(API::class);
		$this->container->singleton(Menu::class);
		$this->container->singleton(ShortcodeManager::class);
		$this->container->singleton(Service::class);
		$this->container->singleton(View::class);
	}

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
	 * Returns `Model` class.
	 *
	 * @return	Model
	 */
	public function model()
	{
		return $this->container->make(Model::class);
	}

	/**
	 * Returns a view from `views/` folder.
	 *
	 * @param	string		$filePath		File path without `.php` extension, where path parts are separated with dots (e.g. `path.to.file`).
	 * @param	array		$passedArray	Input data to pass to the file. The array will be extracted into multiple variables (e.g. `['var1' => 'Foo', 'var2' => 'Bar']`).
	 * @param	bool		$echo			Echo/print the view or just return the view (to save in a variable).
	 *
	 * @return	mixed
	 */
	public function view($filePath, $passedArray = [], $echo = true)
	{
		$view = $this->container->make(View::class);

		if (!$echo) return $view->display($filePath, $passedArray);

		echo $view->display($filePath, $passedArray);
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
		if (empty($this->options))
			$this->options = get_option(FDTBWPB_MENUS_SLUG . '_options');

		return isset($this->options[$optionName]) ? $this->options[$optionName] : null;
	}

	/**
	 * Loads plugin's textdomain.
	 *
	 * @return	void
	 */
	public function i18n()
	{
		load_plugin_textdomain('wordpress-boilerplate-plugin', false, basename(FDTBWPB_DIR) . DIRECTORY_SEPARATOR . 'languages');
	}
}
