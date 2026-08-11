<?php

namespace TelegramPluginBoilerplate\Models;

class ExampleModel extends Base
{
	protected string $table = 'table_name';

	/**
	 * Returns active items. (Example)
	 *
	 * @return	array
	 */
	public function getActiveItems(): array
	{
		return $this->runSelect([], [['name' => 'status', 'value' => 'active', 'type' => parent::TYPE_STRING]]);
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
		$where = $activeOnly ? [['name' => 'status', 'value' => 'active', 'type' => parent::TYPE_STRING]] : [];
		return $this->runCount('id', $where);
	}

	/**
	 * Inserts a new items in the table. (Example)
	 *
	 * @param	bool	$isActive
	 *
	 * @return	int					Inserted row's ID. `0` on error.
	 */
	public function insertItem(bool $isActive = true): int
	{
		return intval($this->runInsert([['name' => 'status', 'value' => $isActive ? 'active' : '', 'type' => parent::TYPE_STRING]]));
	}
}
