<?php

namespace TelegramPluginBoilerplate\Menus;

class SecondMenu extends Base
{
	public static $instance = null;

	protected $menuSlug = FDTBWPB_MENUS_SLUG . '_second';

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
	 * @hooked	filter: `fdtbwpb_menus_submenus` - 10
	 */
	public function addSubmenu($submenus)
	{
		$submenus['second'] = [
			'page_title' => esc_html__('More Boilerplate Settings', 'telegram-plugin-boilerplate'),
			'menu_title' => esc_html__('Second Menu', 'telegram-plugin-boilerplate'),
			'callback'   => [$this, 'displayContent'],
			'position'   => 2,
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
			'general' => esc_html__('General', 'telegram-plugin-boilerplate'),
			'second'  => esc_html__('Second', 'telegram-plugin-boilerplate'),
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
			'example_field_second' => [
				'id'      => 'example_field_second',
				'label'   => esc_html__('Example Field', 'telegram-plugin-boilerplate'),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
			'test_field_second'    => [
				'id'      => 'test_field_second',
				'label'   => esc_html__('Second Tab Field', 'telegram-plugin-boilerplate'),
				'section' => 'second',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
			'test_checkbox_second' => [
				'id'      => 'test_checkbox_second',
				'label'   => esc_html__('Checkbox Field', 'telegram-plugin-boilerplate'),
				'section' => 'second',
				'type'    => 'checkbox',
				'default' => true,
				'args'    => [],
			],
		];
	}
}
