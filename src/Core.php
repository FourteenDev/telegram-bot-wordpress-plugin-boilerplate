<?php

namespace TelegramPluginBoilerplate;

use TelegramPluginBoilerplate\Shortcodes\ShortcodeManager;

class Core
{
	/**
	 * Dependency injection container.
	 *
	 * @var	Container
	 */
	protected Container $container;

	/**
	 * Plugin options.
	 *
	 * @var	array<string, mixed>
	 */
	private array $options = [];

	/**
	 * Constructor.
	 *
	 * @param	Container	$container	Dependency injection container.
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->container->singleton(Core::class, function () { return $this; });

		try {
			$this->registerDependencies();
			$this->setupHooks();
		} catch (\Exception $e) {
			$this->handleInitializationError($e);
		}
	}

	/**
	 * Registers all the dependencies needed for the plugin.
	 *
	 * @return	void
	 */
	private function registerDependencies(): void
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
	 * Sets WordPress hooks and initializes components up.
	 *
	 * @return	void
	 */
	private function setupHooks(): void
	{
		// Initialize dependencies through container
		$this->container->make(Model::class);
		$this->container->make(Asset::class);
		$this->container->make(API::class);

		if (\is_admin())
		{
			$this->container->make(Menu::class);
		} else {
			$this->container->make(ShortcodeManager::class);
		}

		$this->container->make(Service::class);

		\add_action('init', [$this, 'i18n'], 1);
	}

	/**
	 * Handles initialization errors gracefully.
	 *
	 * @param	\Exception	$e	The exception that occurred.
	 *
	 * @return	void
	 */
	private function handleInitializationError(\Exception $e): void
	{
		error_log("Plugin initialization failed: {$e->getMessage()} \n" .
			"file: {$e->getFile()} \n" .
			"line: {$e->getLine()} \n" .
			"trace: {$e->getTraceAsString()}");

		\add_action('admin_notices', function() use ($e)
		{
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e('TelegramPluginBoilerplate: Initialization failed. Please check the error logs.', 'telegram-plugin-boilerplate'); ?></p>
			</div>
			<?php
		});
	}

	/**
	 * Loads plugin's textdomain.
	 *
	 * @return	void
	 *
	 * @hooked	action: `init` - 1
	 */
	public function i18n(): void
	{
		load_plugin_textdomain('telegram-plugin-boilerplate', false, basename(FDTBWPB_DIR) . DIRECTORY_SEPARATOR . 'languages');
	}

	/**
	 * Returns plugin's URL path, without any slashes in the end (e.g. `https://Site.com/wp-content/plugins/my-plugin`).
	 *
	 * @param	string	$path	Path to append to the end of the URL, without any slashes in the beginning (e.g. `path/to/my-file.php`).
	 *
	 * @return	string
	 */
	public function url(string $path = ''): string
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
	public function dir(string $path = ''): string
	{
		return untrailingslashit(FDTBWPB_DIR . $path);
	}

	/**
	 * Returns `Model` class.
	 *
	 * @return	Model
	 */
	public function model(): Model
	{
		return $this->container->make(Model::class);
	}

	/**
	 * Returns a view from `views/` folder.
	 *
	 * @param	string		$filePath		File path without `.php` extension, where path parts are separated with dots (e.g. `path.to.file`).
	 * @param	array		$passedArray	Input data to pass to the file. The array will be extracted into multiple variables (e.g. `['var1' => 'Foo', 'var2' => 'Bar']`).
	 * @param	bool		$echo			Echo/print the view or just return the view.
	 *
	 * @return	string|false|null
	 */
	public function view(string $filePath, array $passedArray = [], bool $echo = true): string|false|null
	{
		$view = $this->container->make(View::class);

		if (!$echo) return $view->display($filePath, $passedArray);

		echo $view->display($filePath, $passedArray);
		return null;
	}

	/**
	 * Returns a plugin option.
	 *
	 * @param	string	$optionName	Option name.
	 * @param	mixed	$default	Default value if option not found.
	 *
	 * @return	mixed
	 */
	public function option(string $optionName, mixed $default = null): mixed
	{
		if (empty($this->options))
			$this->options = get_option(FDTBWPB_MENUS_SLUG . '_options', []);

		return $this->options[$optionName] ?? $default;
	}
}
