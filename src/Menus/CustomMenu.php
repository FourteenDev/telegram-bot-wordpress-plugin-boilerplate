<?php

namespace TelegramPluginBoilerplate\Menus;

class CustomMenu extends Base
{
	public static $instance = null;

	protected $menuSlug = FDTBWPB_MENUS_SLUG . '_custom';

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
		$submenus['custom'] = [
			'page_title' => esc_html__('Custom Menu', 'telegram-plugin-boilerplate'),
			'menu_title' => esc_html__('Custom Menu', 'telegram-plugin-boilerplate'),
			'callback'   => [$this, 'displayContent'],
			'position'   => 3,
		];

		return $submenus;
	}

	/**
	 * Outputs the content for this submenu.
	 *
	 * @return	void
	 */
	public function displayContent()
	{
		FDTBWPB()->view('admin.menus.custom-menu', ['test' => 'Test']);
	}
}
