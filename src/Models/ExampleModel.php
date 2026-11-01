<?php

namespace TelegramPluginBoilerplate\Models;

use TelegramPluginBoilerplate\Model;

class ExampleModel extends Base
{
	protected string $table = 'table_name';

	/**
	 * Constructor.
	 *
	 * @param	Model	$queryBuilder	Query builder instance.
	 *
	 * @example	`$exampleModel = new ExampleModel(FDTBWPB()->model());`
	 */
	public function __construct(Model $queryBuilder)
	{
		parent::__construct($queryBuilder);
	}

	/**
	 * Returns items with `home` in their URL. (Example)
	 *
	 * @return	array
	 */
	public function getHomeUrls(): array
	{
		return $this->selectFrom()->where('url', 'LIKE', '%home%')->getResults();
	}

	/**
	 * Returns count of items in the table. (Example)
	 *
	 * @param	bool	$activeOnly		Count active items only.
	 *
	 * @return	int
	 */
	public function countItems(bool $activeOnly = false): int
	{
		return $this->count($activeOnly ? ['status', '=', 'active'] : []);
	}
}
