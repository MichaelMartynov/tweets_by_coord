<?php

abstract class Model extends ArrayObject implements IModel
{

	public function __get($name)
	{
		return $this[$name];
	}

	public function __set($name, $value)
	{
		$this[$name] = $value;
	}

	/** primary key */
	public static function getPk()
	{
		return 'id';
	}

	/** create table */
	public static function createTable()
	{
		$table = static::getTable();
		$fields = implode(',', static::describe(true));
		$sql = "CREATE TABLE $table (id INTEGER PRIMARY KEY AUTOINCREMENT, $fields)";

		return Db::query($sql);
	}

	/** drop table */
	public static function dropTable()
	{
		$table = static::getTable();
		$sql = "DROP TABLE $table";

		return Db::query($sql);
	}

	/** empty table */
	public static function truncateTable()
	{
		$table = static::getTable();
		$sql = "DELETE FROM $table; VACUUM;";

		return Db::query($sql);
	}

	/** find row by primary key */
	public static function findById($id, $assoc = false)
	{
		return Db::selectRow(static::getTable(), array(static::getPk() => $id), $assoc ? NULL : get_called_class());
	}

	/** find rows by params (where and) */
	public static function find(array $params, $assoc = false)
	{
		return Db::select(static::getTable(), $params, $assoc ? NULL : get_called_class());
	}

	/** find all rows by table */
	public static function findAll($assoc = false)
	{
		return Db::select(static::getTable(), array(), $assoc ? NULL : get_called_class());
	}

	/** find row by params (where or) */
	public static function findOR(array $params, $assoc = false)
	{
		return Db::select(static::getTable(), $params, $assoc ? NULL : get_called_class(), true);
	}

	/** find row by string (LIKE mode) */
	public static function findByString($string, $assoc = false)
	{
		$params = array_fill_keys(static::describe(), $string);

		return Db::select(static::getTable(), $params, $assoc ? NULL : get_called_class(), true, true);
	}

	/** create rows from list */
	public static function createList($list)
	{
		Db::insertList(static::getTable(), $list);

		return true;
	}

	/** update alias */
	public function save()
	{
		return $this->update();
	}

	/** create new row */
	public function create()
	{
		Db::insert(static::getTable(), (array)$this);

		return true;
	}

	/** update row */
	public function update()
	{
		Db::update(static::getTable(), (array)$this, static::getPk(), $this->{self::getPk()});

		return true;
	}

	/** delete row  */
	public function delete()
	{
		Db::delete(static::getTable(), static::getPk(), $this->{static::getPk()});
		unset($this);

		return true;
	}

}
