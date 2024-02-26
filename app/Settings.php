<?php

namespace TelegramPluginBoilerplate;

class Settings
{
	public static $instance = null;

	public static function get_instance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		add_action('admin_menu', [$this, 'create_admin_menu']);
		add_action('admin_init', [$this, 'register_settings']);
		add_filter('plugin_action_links_' . FDTBWPB_BASENAME, [$this, 'actionLinks']);
	}

	/**
	 * Creates a menu in admin dashboard.
	 *
	 * @return	void
	 *
	 * @hooked	action: `admin_menu` - 10
	 */
	public function create_admin_menu()
	{
		add_menu_page(
			esc_html__('Boilerplate Telegram Bot Plugin', FDTBWPB_TEXT_DOMAIN),
			esc_html__('Telegram Bot', FDTBWPB_TEXT_DOMAIN),
			'manage_options',
			'fdtbwpb_settings',
			[$this, 'display_settings_content'],
			'dashicons-heart'
		);
	}

	/**
	 * Outputs the content for settings page.
	 *
	 * @return	void
	 */
	public function display_settings_content()
	{
		FDTBWPB()->view('admin.settings.wrapper');
	}

	/**
	 * Registers bot settings in the admin menu.
	 *
	 * @return	void
	 *
	 * @hooked	action: `admin_init` - 10
	 */
	public function register_settings()
	{
		register_setting('fdtbwpb_settings_group', 'fdtbwpb_options');

		add_settings_section('fdtbwpb_settings_general', esc_html__('General Settings', FDTBWPB_TEXT_DOMAIN), null, 'fdtbwpb_settings_page');
		add_settings_section('fdtbwpb_settings_proxy', esc_html__('Proxy Settings', FDTBWPB_TEXT_DOMAIN), null, 'fdtbwpb_settings_page');

		$settings_fields = [
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

		foreach ($settings_fields as $field)
		{
			$callback = !empty($field['callback']) ? $field['callback'] : [$this, $field['type'] . '_field_callback'];
			$class    = !empty($field['class']) ? implode(' ', $field['class']) : '';
			$args     = ['id' => $field['id'], 'default' => $field['default'], 'css_class' => $class] + $field['args'];

			add_settings_field(
				$field['id'],
				$field['label'],
				$callback,
				'fdtbwpb_settings_page',
				'fdtbwpb_settings_' . $field['section'],
				$args
			);
		}
	}

	/**
	 * Adds plugin action links to the plugins page.
	 *
	 * @param	array	$links
	 *
	 * @return	array
	 *
	 * @hooked	action: `plugin_action_links_{FDTBWPB_BASENAME}` - 10
	 */
	public function actionLinks($links)
	{
		$links[] = '<a href="' . get_admin_url(null, 'admin.php?page=' . FDTBWPB_SETTINGS_SLUG) . '">' . esc_html__('Settings', FDTBWPB_TEXT_DOMAIN) . '</a>';
		return $links;
	}

	/**
	 * Outputs a textbox field.
	 *
	 * @param	array	$args
	 *
	 * @return	string
	 */
	public function text_field_callback($args)
	{
		$id = !empty($args['id']) ? $args['id'] : '';
		if (empty($id)) return;

		FDTBWPB()->view('admin.settings.fields.text', $this->get_settings_value($id, $args));
	}

	/**
	 * Outputs a textarea field.
	 *
	 * @param	array	$args
	 *
	 * @return	string
	 */
	public function textarea_field_callback($args)
	{
		$id = !empty($args['id']) ? $args['id'] : '';
		if (empty($id)) return;

		FDTBWPB()->view('admin.settings.fields.textarea', $this->get_settings_value($id, $args));
	}

	/**
	 * Returns field's value.
	 *
	 * @param	string	$key
	 * @param	array	$args
	 *
	 * @return	string
	 */
	private function get_settings_value($key, $args)
	{
		$default = !empty($args['default']) ? $args['default'] : '';

		$value = FDTBWPB()->option($key);
		if (!empty($value)) $default = $value;

		return [
			'id'          => 'fdtbwpb_options_' . $key,
			'name'        => 'fdtbwpb_options[' . $key . ']',
			'description' => !empty($args['description']) ? trim($args['description']) : '',
			'default'     => $default,
			'readonly'    => !empty($args['readonly']),
			'class'       => $args['css_class'],
		];
	}
}
