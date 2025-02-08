<?php

namespace TelegramPluginBoilerplate\Services\Integrations;

class Elementor
{
	public function __construct()
	{
		add_action('elementor/frontend/before_render', [$this, 'doSomeAction']);
	}

	/**
	 * An example empty method to showcase Elementor integration.
	 *
	 * @param	\Elementor\Element_Base	$element	Current Elementor element.
	 *
	 * @return	void
	 *
	 * @hooked	action: `elementor/frontend/before_render` - 10
	 */
	public function doSomeAction($element)
	{
		if ($element->get_name() !== 'some-element') return;

		// Some action
	}
}
