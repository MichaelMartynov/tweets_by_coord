<?php

abstract class ControllerCrud extends Controller implements IControllerCrud
{

	/** model name for engine */
	protected static function getModelClass()
	{
		return static::getModelName() . "Model";
	}

	/** show list  */
	public function indexAction()
	{
		$modelName = self::getModelClass();
		$list = $modelName::findAll(true);
		$this->display('index', array(
			'list' => $list,
		));
	}

	/** show list by sub string */
	public function findByStrAction($arg, $o = 'html')
	{
		$modelName = self::getModelClass();
		$list = $modelName::findByString($arg, true);
		$data = array(
			'list' => $list,
		);

		$o == 'json' && View::outJsonOk($data);
		$o == 'html' && $this->display('index', $data);
	}

	/** show item by primary key */
	public function findByIdAction($id)
	{
		$modelName = self::getModelClass();
		$obj = $modelName::findById($id, true);

		if ($obj) {
			View::outJsonOk($obj);
		} else
			View::outJsonFail();
	}

	/** show form for create item */
	public function addAction()
	{
		$modelName = self::getModelClass();
		$this->display('add', array(
			'object' => array_fill_keys($modelName::describe(), NULL)
		));
	}

	/** show form for update item */
	public function editAction($id)
	{
		$modelName = self::getModelClass();
		$this->display('edit', array(
			'object' => $modelName::findById($id)
		));
	}

	/** save: create/update item */
	public function saveAction()
	{
		$modelName = self::getModelClass();
		$formData = $_REQUEST['form'];
		if (array_key_exists($modelName::getPk(), $formData)) {
			$id = $formData[$modelName::getPk()];
			if ($object = $modelName::findById($id)) {
				foreach ($modelName::describe() as $key) {
					if (array_key_exists($key, $formData)) {
						$object->{$key} = $formData[$key];
					}
				}
				$object->update();
			} else {
				self::redirectTo("edit/$id");
			}
		} else {
			$object = new $modelName($formData);
			foreach ($modelName::describe() as $key) {
				if (array_key_exists($key, $formData)) {
					$object->{$key} = $formData[$key];
				}
			}
			$object->create();
		}

		self::redirectTo('');
	}

	/** delete item */
	public function deleteAction($id)
	{
		$modelName = self::getModelClass();
		if ($object = $modelName::findById($id))
			if (!$object->delete())
				$this->json(compact('id'));
		self::redirectTo('index');
	}

	/** create table from model describe */
	public function createTableAction()
	{
		$modelName = self::getModelClass();
		if ($modelName::createTable())
			self::redirectTo('');
	}

	/** drop table */
	public function dropTableAction()
	{
		$modelName = self::getModelClass();
		if ($modelName::dropTable())
			self::redirectTo('');
	}

	/** empty table */
	public function truncateTableAction()
	{
		$modelName = self::getModelClass();
		if ($modelName::truncateTable())
			self::redirectTo('');
	}

	/** generate rows */
	final public function generateDataAction()
	{
		$modelClass = self::getModelClass();
		$objects = array();
		for ($i = 0; $i < 100; $i++) {
			$obj = $this->generateObject($modelClass);
			array_push($objects, $obj);
		}
		$modelClass::createList($objects);
		self::redirectTo('');
	}

	protected function generateObject()
	{
		$objectClass = self::getModelClass();
		return array_fill_keys($objectClass::describe(), rand(1,999999));
	}

}