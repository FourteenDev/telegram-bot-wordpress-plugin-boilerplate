<?php

namespace TelegramPluginBoilerplate\Menus;

class SecondMenu extends Base
{
	protected $menuSlug = FDTBWPB_MENUS_SLUG . '_second';

	public function __construct()
	{
		parent::__construct();

		// Uncomment if you want to change select options programmatically
		// add_filter('fdtbwpb_menus_second_fields', [$this, 'populateSelectValues']);

		// Uncomment if you want to sanitize a specific field before saving (Or use `fdtbwpb_before_validate_settings` filter to access all input values)
		// add_filter('fdtbwpb_validate_input_example_field_second', [$this, 'sanitizeExampleField']);
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
		return apply_filters('fdtbwpb_menus_second_fields', [
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
			'test_textarea_field' => [
				'id'      => 'test_textarea_field',
				'label'   => esc_html__('Textarea Field', 'telegram-plugin-boilerplate'),
				'section' => 'second',
				'type'    => 'textarea',
				'default' => '',
				'args'    => [
					'placeholder' => esc_html__('Placeholder', 'telegram-plugin-boilerplate'),
				],
			],
			'test_select_field' => [
				'id'      => 'test_select_field',
				'label'   => esc_html__('Select Field', 'telegram-plugin-boilerplate'),
				'section' => 'second',
				'type'    => 'select',
				'default' => '',
				'args'    => [
					'options'  => [
						// Either keep the options empty here and populate them using the `fdtbwpb_menus_second_fields` filter like below
						// '' => '',

						// Or add options manually yourself
						'key1' => esc_html__('Value 01', 'telegram-plugin-boilerplate'),
						'key2' => esc_html__('Value 02', 'telegram-plugin-boilerplate'),
					],
					// 'multiple' => true,
				],
			],
		]);
	}

	/**
	 * Adds some example values to the select field.
	 *
	 * @param	array	$fields
	 *
	 * @return	array
	 *
	 * @hooked	filter: `fdtbwpb_menus_second_fields` - 10
	 */
	public function populateSelectValues($fields)
	{
		if (empty($fields['test_select_field'])) return $fields;

		$fields['test_select_field']['args']['options'] = [];
		foreach ([1, 2, 3, 4, 5] as $number)
			$fields['test_select_field']['args']['options']["key$number"] = "Value 0$number";

		return $fields;
	}

	/**
	 * Sanitizes the `example_field_second` option.
	 *
	 * @param	string	$value		Submitted value.
	 *
	 * @return	string				Sanitized value.
	 *
	 * @hooked	filter: `fdtbwpb_validate_input_example_field_second` - 10
	 */
	public function sanitizeExampleField($value)
	{
		if ($value == 'this')
			$value = 'that';

		return $value;
	}
}
