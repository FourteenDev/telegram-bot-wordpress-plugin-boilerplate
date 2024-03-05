<?php

namespace TelegramPluginBoilerplate;

class Setting
{
	public static $instance = null;

	private $menuSlug    = FDTBWPB_SETTINGS_SLUG . '_settings';
	private $optionsName = FDTBWPB_SETTINGS_SLUG . '_options';

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		add_action('admin_menu', [$this, 'createAdminMenu']);
		add_action('admin_init', [$this, 'registerSettings']);

		add_filter('plugin_action_links_' . FDTBWPB_BASENAME, [$this, 'actionLinks']);
	}

	/**
	 * Creates a menu in admin dashboard.
	 *
	 * @return	void
	 *
	 * @hooked	action: `admin_menu` - 10
	 */
	public function createAdminMenu()
	{
		add_menu_page(
			esc_html__('Telegram Bot Boilerplate Plugin', FDTBWPB_TEXT_DOMAIN),
			esc_html__('Telegram Bot', FDTBWPB_TEXT_DOMAIN),
			'manage_options',
			$this->menuSlug,
			[$this, 'displaySettingsContent'],
			'dashicons-heart'
		);
	}

	/**
	 * Outputs the content for settings page.
	 *
	 * @return	void
	 */
	public function displaySettingsContent()
	{
		FDTBWPB()->view('admin.settings.wrapper');
	}

	/**
	 * Registers plugin settings in the admin menu.
	 *
	 * @return	void
	 *
	 * @hooked	action: `admin_init` - 10
	 */
	public function registerSettings()
	{
		register_setting("{$this->menuSlug}_group", $this->optionsName);

		add_settings_section("{$this->menuSlug}_general", esc_html__('General Settings', FDTBWPB_TEXT_DOMAIN), null, $this->menuSlug);
		add_settings_section("{$this->menuSlug}_proxy", esc_html__('Proxy Settings', FDTBWPB_TEXT_DOMAIN), null, $this->menuSlug);

		$fields = [
			// General section
			'bot_token' => [
				'id'      => 'bot_token',
				'label'   => esc_html__('Bot token', FDTBWPB_TEXT_DOMAIN),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
			'bot_username' => [
				'id'      => 'bot_username',
				'label'   => esc_html__('Bot username', FDTBWPB_TEXT_DOMAIN),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [
					'description' => esc_html__('With @', FDTBWPB_TEXT_DOMAIN),
				],
			],
			'admin_ids' => [
				'id'      => 'admin_ids',
				'label'   => esc_html__('Admins IDs', FDTBWPB_TEXT_DOMAIN),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [
					'description' => esc_html__('Enter Telegram ID (numeric) of admins, separate IDs with a comma (,).', FDTBWPB_TEXT_DOMAIN),
				],
			],

			// Proxy section
			'proxy_update_receiver' => [
				'id'      => 'proxy_update_receiver',
				'label'   => esc_html__('Update receiver URL', FDTBWPB_TEXT_DOMAIN),
				'section' => 'proxy',
				'type'    => 'text',
				'default' => '',
				'args'    => [
					'description' => esc_html__('Find forward-to-telegram.php that exists in the project root, upload it on a middleman server and enter its full URL here.', FDTBWPB_TEXT_DOMAIN),
				],
			],
		];

		foreach ($fields as $field)
		{
			$callback = !empty($field['callback']) ? $field['callback'] : [$this, $field['type'] . 'FieldCallback'];

			add_settings_field(
				$field['id'],
				$field['label'],
				$callback,
				$this->menuSlug,
				"{$this->menuSlug}_" . $field['section'],
				['id' => $field['id'], 'default' => $field['default']] + $field['args']
			);
		}
	}

	/**
	 * Outputs a text input field.
	 *
	 * @param	array	$args
	 *
	 * @return	string
	 */
	public function textFieldCallback($args)
	{
		$id = !empty($args['id']) ? $args['id'] : '';
		if (empty($id)) return;

		FDTBWPB()->view('admin.settings.fields.text', $this->getSettingsValue($id, $args));
	}

	/**
	 * Outputs a textarea field.
	 *
	 * @param	array	$args
	 *
	 * @return	string
	 */
	public function textareaFieldCallback($args)
	{
		$id = !empty($args['id']) ? $args['id'] : '';
		if (empty($id)) return;

		FDTBWPB()->view('admin.settings.fields.textarea', $this->getSettingsValue($id, $args));
	}

	/**
	 * Returns field's value.
	 *
	 * @param	string	$key
	 * @param	array	$args
	 *
	 * @return	string
	 */
	private function getSettingsValue($key, $args)
	{
		$default = !empty($args['default']) ? $args['default'] : '';

		$value = FDTBWPB()->option($key);
		if (empty($value)) $value = $default;

		return [
			'id'          => "{$this->optionsName}_$key",
			'name'        => "{$this->optionsName}[$key]",
			'description' => !empty($args['description']) ? trim($args['description']) : '',
			'value'       => $value,
		];
	}

	/**
	 * Adds plugin action links to the plugins page.
	 *
	 * @param	array	$links
	 *
	 * @return	array
	 *
	 * @hooked	filter: `plugin_action_links_{FDTBWPB_BASENAME}` - 10
	 */
	public function actionLinks($links)
	{
		$links[] = '<a href="' . get_admin_url(null, "admin.php?page={$this->menuSlug}") . '">' . esc_html__('Settings', FDTBWPB_TEXT_DOMAIN) . '</a>';
		return $links;
	}
}
