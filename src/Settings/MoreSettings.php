<?php

namespace TelegramPluginBoilerplate\Settings;

class MoreSettings extends Base
{
	public static $instance = null;

	protected $menuSlug = FDTBWPB_SETTINGS_SLUG . '_more';

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
		$submenus['more'] = [
			'page_title' => esc_html__('More Boilerplate Settings', 'telegram-plugin-boilerplate'),
			'menu_title' => esc_html__('More Settings', 'telegram-plugin-boilerplate'),
			'callback'   => [$this, 'displayContent'],
			'position'   => 1,
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
		return [
			'general' => esc_html__('More General', 'telegram-plugin-boilerplate'),
			'second'  => esc_html__('More Second', 'telegram-plugin-boilerplate'),
		];
	}

	/**
	 * Returns fields for this submenu.
	 *
	 * @return	array
	 */
	public function getFields()
	{
		return [
			'example_field_more'  => [
				'id'      => 'example_field_more',
				'label'   => esc_html__('More Example Field', 'telegram-plugin-boilerplate'),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
			'test_field_more'     => [
				'id'      => 'test_field_more',
				'label'   => esc_html__('More Second Tab Field', 'telegram-plugin-boilerplate'),
				'section' => 'second',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
			'test_checkbox_field' => [
				'id'      => 'test_checkbox_field',
				'label'   => esc_html__('Checkbox Field', 'telegram-plugin-boilerplate'),
				'section' => 'second',
				'type'    => 'checkbox',
				'default' => true,
				'args'    => [],
			],
		];
	}
}
