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
			'page_title' => esc_html__('More Boilerplate Settings', FDTBWPB_TEXT_DOMAIN),
			'menu_title' => esc_html__('More Settings', FDTBWPB_TEXT_DOMAIN),
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
			'general' => esc_html__('More General', FDTBWPB_TEXT_DOMAIN),
			'second'  => esc_html__('More Second', FDTBWPB_TEXT_DOMAIN),
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
			'example_field_more' => [
				'id'      => 'example_field_more',
				'label'   => esc_html__('More Example Field', FDTBWPB_TEXT_DOMAIN),
				'section' => 'general',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
			'test_field_more'    => [
				'id'      => 'test_field_more',
				'label'   => esc_html__('More Second Tab Field', FDTBWPB_TEXT_DOMAIN),
				'section' => 'second',
				'type'    => 'text',
				'default' => '',
				'args'    => [],
			],
		];
	}
}
