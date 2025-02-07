<?php

namespace TelegramPluginBoilerplate\Models;

abstract class Base
{
	/**
	 * @var	\wpdb
	 */
	protected $wpdb;

	/**
	 * Table name.
	 *
	 * @var	string
	 */
	protected $table = null;

	/*
	 * Data types used in `$where` parameters.
	 *
	 * @todo Add more types and documentation on how to use them.
	 */
	protected const TYPE_INTEGER      = 'int';
	protected const TYPE_BOOLEAN      = 'bool';
	protected const TYPE_STRING       = 'string';
	protected const TYPE_LIKE         = 'like';
	protected const TYPE_NOT_LIKE     = 'not_like';
	protected const TYPE_DATE_BETWEEN = 'date_between';
	protected const TYPE_NULL         = 'null';

	public function __construct()
	{
		if (empty($this->table)) throw new \LogicException(get_class($this) . ' must initialize $table property!');

		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . $this->table;
	}

	/**
	 * Returns the table name.
	 *
	 * @return	string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Returns the result of a SELECT query executed with `get_results`.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$params	Query parameters.
	 *
	 * @return	?array
	 */
	public function getResults($query, $params = [])
	{
		return $this->wpdb->get_results($this->wpdb->prepare($query, ...$params), ARRAY_A);
	}

	/**
	 * Returns a single row from a SELECT query executed with `get_row`.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$params	Query parameters.
	 *
	 * @return	?array
	 */
	public function getRow($query, $params = [])
	{
		return $this->wpdb->get_row($this->wpdb->prepare($query, ...$params), ARRAY_A);
	}

	/**
	 * Returns one value from a SELECT query executed with `get_var`.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$params	Query parameters.
	 *
	 * @return	?string
	 */
	public function getVar($query, $params = [])
	{
		return $this->wpdb->get_var($this->wpdb->prepare($query, ...$params), ARRAY_A);
	}

	/**
	 * Executes an INSERT query.
	 *
	 * @param	array		$data	Data to insert.
	 * @param	array		$format	Format for each value.
	 *
	 * @return	int|false			Inserted row ID, or `false` on failure.
	 */
	public function insert($data, $format = [])
	{
		$this->wpdb->insert($this->table, $data, $format);
		return $this->wpdb->insert_id;
	}

	/**
	 * Executes an UPDATE query.
	 *
	 * @param	array		$data			Data to update.
	 * @param	array		$where			Conditions to find rows to update.
	 * @param	array		$format			Format for each value.
	 * @param	array		$whereFormat	Format for the `WHERE` clause values.
	 *
	 * @return	int|false					Number of affected rows, or `false` on failure.
	 */
	public function update($data, $where, $format = [], $whereFormat = [])
	{
		return $this->wpdb->update($this->table, $data, $where, $format, $whereFormat);
	}

	/**
	 * Executes a DELETE query.
	 *
	 * @param	array		$where			Conditions to find rows to delete.
	 * @param	array		$whereFormat	Format for the `WHERE` clause values.
	 *
	 * @return	int|false					Number of deleted rows, or `false` on failure.
	 */
	public function delete($where, $whereFormat = [])
	{
		return $this->wpdb->delete($this->table, $where, $whereFormat);
	}

	/**
	 * Executes a custom SQL query.
	 *
	 * @param	string		$query	SQL query.
	 * @param	array		$params	Query parameters.
	 *
	 * @return	int|bool			`true` for `CREATE`, `ALTER`, `TRUNCATE` and `DROP`. Number of affected/selected rows for all other queries. `false` on error.
	 */
	public function query($query, $params = [])
	{
		return $this->wpdb->query($this->wpdb->prepare($query, ...$params));
	}

	/**
	 * Returns the last inserted ID.
	 *
	 * @return	int
	 */
	public function getLastInsertId()
	{
		return $this->wpdb->insert_id;
	}

	/**
	 * Returns the last error message.
	 *
	 * @return	string
	 */
	public function getLastError()
	{
		return $this->wpdb->last_error;
	}

	/**
	 * Selects values from the database.
	 *
	 * @param	array		$columns
	 * @param	array		$where		An array of associative arrays with this format: `ColumnName, Value, Type`.
	 * 	Example:
	 * 	```
	 * 	[
	 * 		['name' => 'id', 'value' => 12345, 'type' => parent::TYPE_INTEGER],
	 * 		['name' => 'name', 'value' => 'test', 'type' => parent::TYPE_STRING],
	 * 		['name' => 'private', 'value' => true, 'type' => parent::TYPE_BOOLEAN],
	 * 	]
	 * 	```
	 * 	If the value is `null`, that index in the array will be ignored.
	 * 	For the type value, use `Base::TYPE_...` constants.
	 * @param	int			$limit
	 * @param	int			$offset
	 * @param	string		$orderBy	Name of the column to order by.
	 * @param	string		$orderType	Either 'ASC' or 'DESC'.
	 *
	 * @return	?array					Database query results.
	 */
	public function runSelect($columns = [], $where = [], $limit = 0, $offset = 0, $orderBy = '', $orderType = 'DESC')
	{
		$query = 'SELECT ';
		if (!empty($columns) && is_array($columns)) $query .= implode(', ', $columns);
		else $query .= '*';

		$query     .= " FROM {$this->table} WHERE 1";
		$parameters = [];

		foreach ($where as $currentCondition)
		{
			if (!isset($currentCondition['name']) || !isset($currentCondition['value']) || !isset($currentCondition['type'])) continue;

			if ($currentCondition['type'] === self::TYPE_STRING && !empty($currentCondition['value']))
			{
				$query       .= ' AND `' . $currentCondition['name'] . '` = %s';
				$parameters[] = trim($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_LIKE && !empty($currentCondition['value'])) {
				$query       .= ' AND `' . $currentCondition['name'] . '` LIKE %s';
				$parameters[] = trim($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_NOT_LIKE && !empty($currentCondition['value'])) {
				$query       .= ' AND `' . $currentCondition['name'] . '` NOT LIKE %s';
				$parameters[] = trim($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_INTEGER && intval($currentCondition['value'])) {
				$query       .= ' AND `' . $currentCondition['name'] . '` = %d';
				$parameters[] = intval($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_BOOLEAN && ($currentCondition['value'] === true || $currentCondition['value'] === false)) {
				$query       .= ' AND `' . $currentCondition['name'] . '` = %d';
				$parameters[] = $currentCondition['value'] ? 1 : 0;
			} else if ($currentCondition['type'] === self::TYPE_DATE_BETWEEN && !empty($currentCondition['value']) && is_array($currentCondition['value']) && !empty($currentCondition['value']['from']) && !empty($currentCondition['value']['to'])) {
				$query       .= ' AND (`' . $currentCondition['name'] . '` BETWEEN %s AND %s)';
				$parameters[] = $currentCondition['value']['from'];
				$parameters[] = $currentCondition['value']['to'];
			} else if ($currentCondition['type'] === self::TYPE_NULL) {
				$query       .= ' AND `' . $currentCondition['name'] . ' IS NULL';
			}
		}

		if (!empty($orderBy))
		{
			$query .= " ORDER BY $orderBy ";
			$query .= strtolower($orderType) === 'asc' ? 'ASC' : 'DESC';
		}

		if (intval($limit))
		{
			$query .= " LIMIT $limit";
			if (intval($offset)) $query .= " OFFSET $limit";
		}

		$query .= ';';

		return (intval($limit) === 1) ? $this->getRow($query, $parameters) : $this->getResults($query, $parameters);
	}

	/**
	 * Selects values from the database.
	 *
	 * @param	array		$columnName
	 * @param	array		$where		An array of associative arrays with this format: `ColumnName, Value, Type`.
	 * 	Example:
	 * 	```
	 * 	[
	 * 		['name' => 'id', 'value' => 12345, 'type' => parent::TYPE_INTEGER],
	 * 		['name' => 'name', 'value' => 'test', 'type' => parent::TYPE_STRING],
	 * 		['name' => 'private', 'value' => true, 'type' => parent::TYPE_BOOLEAN],
	 * 	]
	 * 	```
	 * 	If the value is `null`, that index in the array will be ignored.
	 * 	For the type value, use `Base::TYPE_...` constants.
	 *
	 * @return	int
	 */
	public function runCount($columnName = '*', $where = [])
	{
		$query      = "SELECT COUNT(`$columnName`) FROM {$this->table} WHERE 1";
		$parameters = [];

		foreach ($where as $currentCondition)
		{
			if (!isset($currentCondition['name']) || !isset($currentCondition['value']) || !isset($currentCondition['type'])) continue;

			if ($currentCondition['type'] === self::TYPE_STRING && !empty($currentCondition['value']))
			{
				$query       .= ' AND `' . $currentCondition['name'] . '` = %s';
				$parameters[] = trim($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_LIKE && !empty($currentCondition['value'])) {
				$query       .= ' AND `' . $currentCondition['name'] . '` LIKE %s';
				$parameters[] = trim($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_NOT_LIKE && !empty($currentCondition['value'])) {
				$query       .= ' AND `' . $currentCondition['name'] . '` NOT LIKE %s';
				$parameters[] = trim($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_INTEGER && intval($currentCondition['value'])) {
				$query       .= ' AND `' . $currentCondition['name'] . '` = %d';
				$parameters[] = intval($currentCondition['value']);
			} else if ($currentCondition['type'] === self::TYPE_BOOLEAN && ($currentCondition['value'] === true || $currentCondition['value'] === false)) {
				$query       .= ' AND `' . $currentCondition['name'] . '` = %d';
				$parameters[] = $currentCondition['value'] ? 1 : 0;
			} else if ($currentCondition['type'] === self::TYPE_DATE_BETWEEN && !empty($currentCondition['value']) && is_array($currentCondition['value']) && !empty($currentCondition['value']['from']) && !empty($currentCondition['value']['to'])) {
				$query       .= ' AND (`' . $currentCondition['name'] . '` BETWEEN %s AND %s)';
				$parameters[] = $currentCondition['value']['from'];
				$parameters[] = $currentCondition['value']['to'];
			} else if ($currentCondition['type'] === self::TYPE_NULL) {
				$query       .= ' AND `' . $currentCondition['name'] . '` IS NULL';
			}
		}

		$query .= ';';

		return intval($this->getVar($query, $parameters));
	}

	/**
	 * Inserts new values in the database.
	 *
	 * @param	array		$data	An array of associative arrays with this format: `ColumnName, Value, Type`.
	 * 	Example:
	 * 	```
	 * 	[
	 * 		['name' => 'id', 'value' => 12345, 'type' => parent::TYPE_INTEGER],
	 * 		['name' => 'name', 'value' => 'test', 'type' => parent::TYPE_STRING],
	 * 		['name' => 'private', 'value' => true, 'type' => parent::TYPE_BOOLEAN],
	 * 	]
	 * 	```
	 * 	If the value is `null`, that index in the array will be ignored.
	 * 	For the type value, use `Base::TYPE_...` constants.
	 *
	 * @return	int|false|null		Inserted row's ID.
	 */
	public function runInsert($data)
	{
		if (empty($data) || !is_array($data)) return null;

		$parsedData = [];
		foreach ($data as $currentData)
		{
			if (!isset($currentData['name']) || !isset($currentData['value']) || !isset($currentData['type'])) continue;

			if ($currentData['type'] === self::TYPE_STRING && !empty($currentData['value']))
				$parsedData[$currentData['name']] = trim($currentData['value']);
			else if ($currentData['type'] === self::TYPE_INTEGER && intval($currentData['value']))
				$parsedData[$currentData['name']] = intval($currentData['value']);
			else if ($currentData['type'] === self::TYPE_BOOLEAN && ($currentData['value'] === true || $currentData['value'] === false))
				$parsedData[$currentData['name']] = $currentData['value'] ? 1 : 0;
		}
		if (!count($parsedData)) return null;

		return $this->insert($parsedData);
	}
}
