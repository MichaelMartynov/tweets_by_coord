<?php

abstract class ViewCrud extends View
{

	public static function initMenu($itemActive = 'index', $title = '')
	{
		if (!self::$menu) {
			parent::initMenu();
		}
		$viewName = strtolower(static::getName());
		$menu = array(
			'index'         => array(
				'link'  => Route::createUrl("/$viewName"),
				'title' => 'Таблица'
			),
			'add'           => array(
				'link'  => Route::createUrl("/$viewName/add"),
				'title' => 'Добавить запись'
			),
			'truncateTable' => array(
				'link'  => Route::createUrl("/$viewName/truncateTable"),
				'title' => 'Очистить таблицу'
			),
			'generateData'  => array(
				'link'  => Route::createUrl("/$viewName/generateData"),
				'title' => 'Сгенерировать 100 записей'
			)
		);

		if (array_key_exists($itemActive, $menu)) {
			$menu[$itemActive]['active'] = true;
			self::$pageTitle .= ' / ' . $menu[$itemActive]['title'];
		} else {
			self::$pageTitle .= ' / ' . $title;
		}
		self::setGlobal(array(
			'crudmenu'  => $menu,
			'pageTitle' => self::$pageTitle
		));

	}

	/**
	 * @param $list Model[]
	 */
	public static function index($list)
	{
		self::initMenu('index');

		$viewName = strtolower(static::getName());
		self::template('index', array(
			'deleteLink' => "/$viewName/delete",
			'editLink'   => "/$viewName/edit",
			'list'       => $list
		));
	}

	public static function add($object)
	{
		self::initMenu('add');

		self::template('form', array(
			'object' => $object
		));
	}

	public static function edit($object)
	{
		self::initMenu('edit', 'Редактирование');
		self::template('form', array(
			'object' => $object
		));
	}


}