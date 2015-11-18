<?php

interface IModel
{

	/** получение названия таблицы */
	static function getTable();
	/** получение списка полей */
	static function describe();

}