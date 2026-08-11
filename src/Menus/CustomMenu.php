<?php

namespace TelegramPluginBoilerplate\Menus;

class CustomMenu extends Base
{
	protected $menuSlug = FDTBWPB_MENUS_SLUG . '_custom';

	/**
	 * Adds the submenu.
	 *
	 * @param	array	$submenus
	 *
	 * @return	array
	 *
	 * @hooked	filter: `fdtbwpb_menus_submenus` - 10
	 */
	public function addSubmenu($submenus): array
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
	public function displayContent(): void
	{
		FDTBWPB()->view('admin.menus.custom-menu', ['test' => 'Test']);
	}
}
