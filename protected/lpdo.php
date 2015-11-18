<?php

function _event(PDOException $ex)
{
	outvar($ex);
}

/** PDO - light sql queries */
class lPDO
{
	protected $connect;

	/** create connect instance */
	public function __construct($dsn, $username, $password)
	{
		$this->connect = new PDO($dsn, $username, $password);
		$this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/** execute customer query without sql injection defence */
	public function query($sql)
	{
		return $this->connect->query($sql);
	}

	/** get row/object from $table by $params */
	public function selectRow($table, array $params, $class = NULL)
	{
		$params = $this->select($table, $params, $class, false, false, 1);

		return current($params);
	}

	/** find rows/objects from $table by $params */
	public function select($table, array $params, $class = NULL, $or = false, $like = false, $limit = 0)
	{
		$fieldNames = array();
		$values = array();
		array_walk($params, function ($value, $key) use (&$fieldNames, &$values, $like) {
			$fieldNames[] = $like ? "$key LIKE :$key" : "$key=:$key";
			$values[":$key"] = $like ? '%' . $value . '%' : $value;
		});
		$where = $fieldNames ? ("WHERE " . implode($or ? ' OR ' : ' AND ', $fieldNames)) : "";

		$sql = "SELECT * FROM $table $where";
		$stmt = $this->connect->prepare($sql);
		$stmt->execute($values);

		if ($class)
			return $stmt->fetchAll(PDO::FETCH_CLASS, $class);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/** insert row to table */
	public function insert($table, array $row)
	{
		$fieldNames = array_keys($row);
		$fields = implode(' ,', $fieldNames);
		$bound = implode(', :', $fieldNames);

		$sql = "INSERT INTO $table ($fields)  VALUES  (:$bound)";
		$stmt = $this->connect->prepare($sql);

		$params = array();
		array_walk($row, function ($value, $key) use (&$params) {
			$params[":$key"] = $value;
		});
		$stmt->execute($params);
	}

	/** insert list to table */
	public function insertList($table, array $rows)
	{
		$fieldNames = array_keys(current($rows));
		$fields = implode(' ,', $fieldNames);
		$bound = implode(', :', $fieldNames);

		$sql = "INSERT INTO $table ($fields)  VALUES  (:$bound)";
		$stmt = $this->connect->prepare($sql);

		foreach ($rows as &$item) {
			$params = array();
			array_walk($item, function ($value, $key) use (&$params) {
				$params[":$key"] = $value;
			});
			$stmt->execute($params);
		}
	}

	/** update row on table */
	public function update($table, array $row, $pkName, $pkValue)
	{
		$fieldNames = array();
		$params = array();
		array_walk($row, function ($value, $key) use (&$fieldNames, &$params) {
			$fieldNames[] = "$key = :$key";
			$params[":$key"] = $value;
		});

		$set = implode(', ', $fieldNames);
		$params[":$pkName"] = $pkValue;
		$sql = "UPDATE $table SET $set WHERE $pkName = :$pkName";
		$stmt = $this->connect->prepare($sql);
		$stmt->execute($params);
	}

	/** delete row from table */
	public function delete($table, $pkName, $pkValue)
	{
		$params = array(
			":$pkName" => $pkValue
		);
		$sql = "DELETE FROM $table WHERE $pkName = :$pkName";
		$stmt = $this->connect->prepare($sql);
		$stmt->execute($params);
	}
}