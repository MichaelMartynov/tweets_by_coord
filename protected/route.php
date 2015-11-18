<?php

class Route
{

	const controlDefault = 'index';
	const methodDefault = 'index';

	public static $control;
	public static $method;
	public static $params = Array();
	public static $path = Array();


	private static function init()
	{
		$request = explode('?', trim($_SERVER['REQUEST_URI'], "/"));
		$path = explode('/', current($request));
		if ($path[0] == 'index.php') array_shift($path);

		foreach ($_REQUEST as $param => $value) {
			if (!is_array($value) && !is_object($value)) {
				$newval = json_decode($value);
				if (is_array($newval) || is_object($newval)) {
					$_REQUEST[$param] = $newval;
				}
			}
		}

		$control = array_shift($path);
		$method = array_shift($path);

		self::$control = ucfirst($control ? $control : self::controlDefault) . 'Controller';
		self::$method = lcfirst($method ? $method : self::methodDefault) . 'Action';
		self::$path = $path;
		self::$params = $_REQUEST;

	}

	public static function exec()
	{
		try {
			self::init();
			self::$params = self::sortParams(self::$control, self::$method, self::$params);
			$controller = self::$control;

			$controller = new $controller();
			$controller->path = self::$path;
			call_user_func_array(array($controller, self::$method), self::$params);
		} catch (ReflectionException $ex) {
			throw $ex;
		}
	}

	public static function createUrl($uri)
	{
		if (strpos($uri, '/') !== 0) {
			$uri = $_SERVER['REQUEST_URI'] . '/' . $uri;
		}

		return 'http://' . $_SERVER['HTTP_HOST'] . $uri;
	}

	public static function redirect($to)
	{
		if ($to != self:: uri()) {
			header('HTTP/1.1 307 Moved Permanently');
			header('Location: ' . $to);
			exit;
		}
	}

	public static function getRouteUri()
	{
		return strtolower(preg_replace('/controller/i', '', self::$control).'/'.preg_replace('/action/i', '', self::$method));
	}

	public static function uri()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public static function sortParams($class, $method, $params)
	{
		$refl = new ReflectionMethod($class, $method);
		$method_params = $refl->getParameters();

		if (!!$method_params) {
			$sorted_params = array();
			foreach ($method_params as $param) {
				foreach ($params as $name => $value) {
					if ($param->getName() == $name) {
						if (!$param->allowsNull() and $value == NULL) {
							throw new Exception("Parameter '" . $param->getName() . "' of method '" . self::$method . "' in class '" . self::$control . "' can not be empty");
						} else {
							$sorted_params[$param->getName()] = is_string($value) ? trim($value) : $value;
						}
					}
				}
				if (!array_key_exists($param->getName(), $sorted_params)) {
					if ($param->isDefaultValueAvailable()) {
						$sorted_params[$param->getName()] = $param->getDefaultValue();
					} else {
						throw new Exception("Parameter '" . $param->getName() . "' of method '" . self::$method . "' in class '" . self::$control . "' not given");
					}
				}
			}
			foreach ($method_params as $p) {
				$pn = $p->getName();
				$sp[$pn] = $sorted_params[$pn];
			}
			$params = $sp;
		}

		return $params;
	}
}