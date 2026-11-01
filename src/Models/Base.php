<?php

namespace TelegramPluginBoilerplate\Models;

use TelegramPluginBoilerplate\Model;

/**
 * Base model for database tables.
 */
abstract class Base
{
	/** @var	\wpdb */
	protected \wpdb $wpdb;

	/** @var	Model */
	protected Model $queryBuilder;

	/**
	 * Table name.
	 *
	 * @var	string
	 */
	protected string $table = '';

	/**
	 * Constructor.
	 *
	 * @param	Model	$queryBuilder	Query builder instance.
	 */
	public function __construct(Model $queryBuilder)
	{
		if (empty($this->table))
			throw new \LogicException(get_class($this) . ' must initialize $table property!');

		$this->queryBuilder = $queryBuilder;
	}

	/**
	 * Returns the table name.
	 *
	 * @return	string
	 */
	public function getTable(): string
	{
		return $this->table;
	}

	/**
	 * Applies simple `column` => `value` `WHERE` conditions to a query.
	 *
	 * @param	Model	$queryBuilder	Query builder.
	 * @param	array	$where			Simple `column` => `value` conditions.
	 *
	 * @return	void
	 */
	protected function applyWhere(Model $queryBuilder, array $where): void
	{
		foreach ($where as $column => $value)
		{
			if (is_array($value))
				$queryBuilder->where($column, 'IN', $value);
			else if (is_null($value))
				$queryBuilder->whereRaw("`$column` IS NULL");
			else if (is_bool($value))
				$queryBuilder->where($column, '=', $value ? 1 : 0);
			else
				$queryBuilder->where($column, '=', $value);
		}
	}

	/**
	 * Returns a simple `SELECT * FROM {table}` query scoped to this table for further chaining.
	 *
	 * @return	Model
	 */
	public function selectFrom(): Model
	{
		return $this->queryBuilder->select('*')->from($this->table);
	}

	/**
	 * Returns all rows matching the conditions.
	 *
	 * @param	string|array	$fields		Fields to return. Defaults to `*`.
	 * @param	array|null		$where		Simple `column` => `value` conditions.
	 * @param	string|null		$orderBy	Order column.
	 * @param	string			$orderType	Acceptable values: 'ASC' or 'DESC'.
	 * @param	int				$limit		Max rows.
	 * @param	int				$offset		Offset.
	 *
	 * @return	array|null					Database query results. Empty array when no rows match or on database error. `null` when the query is empty or when the connection is not ready.
	 */
	public function findAll(string|array $fields = '*', ?array $where = null, ?string $orderBy = null, string $orderType = 'DESC', int $limit = 0, int $offset = 0): ?array
	{
		if (empty($fields) || $fields === '*')
			$queryBuilder = $this->selectFrom();
		else
			$queryBuilder = $this->queryBuilder->select(is_array($fields) ? implode(', ', $fields) : trim($fields))->from($this->table);

		if (!empty($where))
			$this->applyWhere($queryBuilder, $where);

		if (!empty($orderBy))
			$queryBuilder->orderBy($orderBy, $orderType);

		if (intval($limit))
			$queryBuilder->limit($limit, $offset);

		return $queryBuilder->getResults();
	}

	/**
	 * Returns a single row matching the conditions.
	 *
	 * @param	string|array	$fields		Fields to return. Defaults to `*`.
	 * @param	array|null		$where		Simple `column` => `value` conditions.
	 *
	 * @return	array|null					Database query result or `null` on failure.
	 */
	public function find(string|array $fields = '*', ?array $where = null): ?array
	{
		if (empty($fields) || $fields === '*')
			$queryBuilder = $this->selectFrom();
		else
			$queryBuilder = $this->queryBuilder->select(is_array($fields) ? implode(', ', $fields) : trim($fields))->from($this->table);

		if (!empty($where))
			$this->applyWhere($queryBuilder, $where);

		return $queryBuilder->limit(1)->getRow();
	}

	/**
	 * Returns count of rows matching the conditions.
	 *
	 * @param	array|null	$where	Simple `column` => `value` conditions.
	 *
	 * @return	int
	 */
	public function count(?array $where = null): int
	{
		$queryBuilder = $this->queryBuilder->select('COUNT(*) AS `count`')->from($this->table);

		if (!empty($where))
			$this->applyWhere($queryBuilder, $where);

		return intval($queryBuilder->getVar());
	}

	/**
	 * Inserts a new row.
	 *
	 * @param	array	$data	`column` => `value` pairs.
	 *
	 * @return	int|false		Inserted row ID or `false` on error.
	 */
	public function create(array $data): int|false
	{
		if (empty($data)) return false;

		$this->queryBuilder->insert($this->table)->set($data);

		return ($this->queryBuilder->execute() !== false) ? intval($this->queryBuilder->getLastInsertId()) : false;
	}

	/**
	 * Updates rows matching the conditions.
	 *
	 * @param	array	$data	`column` => `value` pairs to update.
	 * @param	array	$where	Simple `column` => `value` conditions.
	 *
	 * @return	int|false		Affected rows or `false` on error.
	 */
	public function update(array $data, array $where): int|false
	{
		if (empty($data) || empty($where)) return false;

		$this->queryBuilder->update($this->table)->set($data);
		$this->applyWhere($this->queryBuilder, $where);

		return $this->queryBuilder->execute();
	}

	/**
	 * Deletes rows matching the conditions.
	 *
	 * @param	array	$where	Simple `column` => `value` conditions.
	 *
	 * @return	int|false		Deleted rows or `false` on error.
	 */
	public function delete(array $where): int|false
	{
		if (empty($where)) return false;

		$this->queryBuilder->delete($this->table);
		$this->applyWhere($this->queryBuilder, $where);

		return $this->queryBuilder->execute();
	}
}
