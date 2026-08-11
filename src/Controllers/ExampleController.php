<?php

namespace TelegramPluginBoilerplate\Controllers;

use TelegramPluginBoilerplate\Models\ExampleModel;

class ExampleController
{
	private ExampleModel $exampleModel;

	public function __construct(ExampleModel $exampleModel)
	{
		$this->exampleModel = $exampleModel;
	}

	/**
	 * Returns active items. (Example)
	 *
	 * @return	array
	 */
	public function getActiveItems(): array
	{
		return $this->exampleModel->getActiveItems();
	}
}
