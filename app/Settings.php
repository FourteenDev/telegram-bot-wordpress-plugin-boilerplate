<?php

namespace BoilerplateTelegramPlugin;

class Settings
{
	public static $instance = null;

	public static function get_instance()
	{
		null === self::$instance && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		add_action('admin_menu', [$this, 'create_admin_menu']);
		add_action('admin_init', [$this, 'register_settings']);
		add_filter('plugin_action_links_' . BTBP_BASENAME, [$this, 'actionLinks']);
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
			esc_html__('Boilerplate Telegram Bot Plugin', BTBP_TEXT_DOMAIN),
			esc_html__('Telegram Bot', BTBP_TEXT_DOMAIN),
			'manage_options',
			'btbp_settings',
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
		BTBP()->view('admin.settings.wrapper');
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
		register_setting('btbp_settings_group', 'btbp_options');

		add_settings_section('btbp_settings_general', esc_html__('General Settings', BTBP_TEXT_DOMAIN), null, 'btbp_settings_page');

		$settings_fields = [
			'bot_token' => [
				'id' 		=> 'bot_token',
				'label' 	=> esc_html__('Bot token', BTBP_TEXT_DOMAIN),
				'section' 	=> 'general',
				'type' 		=> 'text',
				'default' 	=> '',
				'args' 		=> [],
			],
			'bot_username' => [
				'id' 		=> 'bot_username',
				'label' 	=> esc_html__('Bot username', BTBP_TEXT_DOMAIN),
				'section' 	=> 'general',
				'type' 		=> 'text',
				'default' 	=> '',
				'args' 		=> [],
			],
		];

		foreach ($settings_fields as $field)
		{
			$callback 	= !empty($field['callback']) ? $field['callback'] : [$this, $field['type'] . '_field_callback'];
			$class 		= !empty($field['class']) ? implode(' ', $field['class']) : '';
			$args 		= ['id' => $field['id'], 'default' => $field['default'], 'css_class' => $class] + $field['args'];

			add_settings_field(
				$field['id'],
				$field['label'],
				$callback,
				'btbp_settings_page',
				'btbp_settings_' . $field['section'],
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
	 * @hooked	action: `plugin_action_links_{BTBP_BASENAME}` - 10
	 */
	public function actionLinks($links)
	{
		$links[] = '<a href="' . get_admin_url(null, 'admin.php?page=' . BTBP_SETTINGS_SLUG) . '">' . esc_html__('Settings', BTBP_TEXT_DOMAIN) . '</a>';
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

		$default 	= !empty($args['default']) ? $args['default'] : '';
		$readonly 	= !empty($args['readonly']);

		$view_args 			= $this->get_settings_value($id, $default, '', '', $readonly);
		$view_args['class'] = $args['css_class'];

		BTBP()->view('admin.settings.fields.text', $view_args);
	}

	/**
	 * Returns field's value.
	 *
	 * @param	array	$args
	 *
	 * @return	string
	 */
	private function get_settings_value($key, $default = '', $options = [], $checkbox_value = '', $readonly = false)
	{
		$value = BTBP()->option($key);
		if (!empty($value)) $default = $value;

		return [
			'default' 			=> $default,
			'name' 				=> 'btbp_options[' . $key . ']',
			'id' 				=> 'btbp_options_' . $key,
			'options' 			=> $options,
			'checkbox_value' 	=> $checkbox_value, 
			'readonly' 			=> $readonly,
		];
	}
}
