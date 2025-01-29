<?php

namespace TelegramPluginBoilerplate\Settings;

class MainSettings extends Base
{
	public static $instance = null;

	protected $menuSlug = FDTBWPB_SETTINGS_SLUG . '_settings';

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Adds the submenu.
	 *
	 * @param	array	$submenus
	 *
	 * @return	array
	 *
	 * @hooked	filter: `fdtbwpb_settings_submenus` - 10
	 */
	public function addSubmenu($submenus)
	{
		$submenus['settings'] = [
			'page_title' => esc_html__('Telegram Bot Boilerplate Plugin', 'telegram-plugin-boilerplate'),
			'menu_title' => esc_html__('Telegram Bot', 'telegram-plugin-boilerplate'),
			'callback'   => [$this, 'displayContent'],
			'position'   => 0,
		];

		return $submenus;
	}

	/**
	 * Returns tabs for this submenu.
	 *
	 * @return	array
	 */
	public function getTabs()
	{
		return apply_filters('fdtbwpb_settings_main_tabs', [
			'general' => esc_html__('General Settings', 'telegram-plugin-boilerplate'),
			'proxy'   => esc_html__('Proxy Settings', 'telegram-plugin-boilerplate'),
		]);
	}

	/**
	 * Returns fields for this submenu.
	 *
	 * @return	array
	 */
	public function getFields()
	{
		return apply_filters('fdtbwpb_settings_main_fields', [
			// General section
			'bot_token' => [
				'id'      => 'bot_token',
				'label'   => esc_html__('Bot token', 'telegram-plugin-boilerplate'),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
			'bot_username' => [
				'id'      => 'bot_username',
				'label'   => esc_html__('Bot username', 'telegram-plugin-boilerplate'),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [
					'description' => esc_html__('With @', 'telegram-plugin-boilerplate'),
				],
			],
			'admin_ids' => [
				'id'      => 'admin_ids',
				'label'   => esc_html__('Admins IDs', 'telegram-plugin-boilerplate'),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [
					'description' => esc_html__('Enter Telegram ID (numeric) of admins, separate IDs with a comma (,).', 'telegram-plugin-boilerplate'),
				],
			],

			// Proxy section
			'proxy_update_receiver' => [
				'id'      => 'proxy_update_receiver',
				'label'   => esc_html__('Update receiver URL', 'telegram-plugin-boilerplate'),
				'section' => 'proxy',
				'type'    => 'text',
				'default' => '',
				'args'    => [
					'description' => esc_html__('Find forward-to-telegram.php that exists in the project root, upload it on a middleman server and enter its full URL here.', 'telegram-plugin-boilerplate'),
				],
			],
		]);
	}
}
