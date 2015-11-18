<?php

class Controller
{
	public $path = array();

	/** return controller name */
	final protected function getName()
	{
		if (preg_match('/^(.+)controller$/i', get_called_class(), $m))
			return $m[1];
	}

	/** redirect to $action */
	final public function redirectTo($action, array $params = array())
	{
		Route::redirect("/" . strtolower(self::getName())
			. "/" . $action
			. ($params ? ("?" . http_build_query($params)) : ""));
	}

	/** View :: template */
	final protected function display($template, $params = array())
	{
		$classController = get_called_class();
		$className = str_replace(__CLASS__, '', $classController);
		$classView = $className . 'View';
		if (class_exists($classView) && method_exists($classView, $template)) {
			// call `$classView`::`$template` method if method exist
			call_user_func_array(array($classView, $template), Route::sortParams($classView, $template, $params));
		} else {
			// call `$classView`::`template` method
			call_user_func_array(array($classView, 'template'), array( $template, $params));
		}
	}

}