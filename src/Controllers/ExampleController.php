<?php

namespace TelegramPluginBoilerplate\Controllers;

use TelegramPluginBoilerplate\Models\ExampleModel;

class ExampleController
{
	private $exampleModel;

	public function __construct()
	{
		$this->exampleModel = new ExampleModel();
	}

	/**
	 * Returns active items. (Example)
	 *
	 * @return	array
	 */
	public function getActiveItems()
	{
		return $this->exampleModel->getActiveItems();
	}
}
