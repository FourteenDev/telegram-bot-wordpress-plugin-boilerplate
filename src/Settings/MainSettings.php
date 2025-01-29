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
			'page_title' => esc_html__('Telegram Bot Boilerplate Plugin', FDTBWPB_TEXT_DOMAIN),
			'menu_title' => esc_html__('Telegram Bot', FDTBWPB_TEXT_DOMAIN),
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
			'general' => esc_html__('General Settings', FDTBWPB_TEXT_DOMAIN),
			'proxy'   => esc_html__('Proxy Settings', FDTBWPB_TEXT_DOMAIN),
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
		]);
	}
}
