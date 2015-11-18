<?php

/** lPDO static wrapper */
class Db
{
	/** @var lPDO */
	protected static $db;

	/**
	 * connect to database for use static methods
	 *
	 * @param null $dsn
	 * @param null $user
	 * @param null $pass
	 *
	 * @return lPDO
	 */
	public static function connect($dsn, $user = NULL, $pass = NULL)
	{
		self::$db = new lPDO($dsn, $user, $pass);
	}

	/** @return lPDO */
	protected static function getDB()
	{
		if(!self::$db)
			throw new Exception('DB not connected');
		return self::$db;
	}

	public static function query($sql)
	{
		return self::getDB()->query($sql);
	}

	public static function selectRow($table, array $params, $class = NULL)
	{
		return self::getDB()->selectRow($table, $params, $class);
	}

	public static function select($table, array $params, $class = NULL, $or = false, $like = false, $limit = 0)
	{
		return self::getDB()->select($table, $params, $class, $or, $like, $limit);
	}

	public static function insert($table, array $row)
	{
		return self::getDB()->insert($table, $row);
	}

	public static function insertList($table, array $rows)
	{
		return self::getDB()->insertList($table, $rows);
	}

	public static function update($table, array $row, $pkName, $pkValue)
	{
		return self::getDB()->update($table, $row, $pkName, $pkValue);
	}

	public static function delete($table, $pkName, $pkValue)
	{
		return self::getDB()->delete($table, $pkName, $pkValue);
	}
}