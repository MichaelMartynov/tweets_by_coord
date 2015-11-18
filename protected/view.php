<?php

class View
{

	/** @var mixed[] template engine instances */
	protected static $engineInstance;

	/** @var string[] global params */
	protected static $params = array();

	/** menu from config/menu.inc.php */
	protected static $menu = array();

	/** @var title */
	protected static $title;
	/** @var page title */
	protected static $pageTitle;

	/** set global variables for template engine */
	final public static function setGlobal(array $params = array())
	{
		self::$params = array_merge(self::$params, $params);
	}

	/** render template */
	final public static function template($template, array $params = array())
	{
		$classView = get_called_class();
		// при вызове из наследника - подставить в название шаблона папку
		$className = str_replace(__CLASS__, '', $classView);
		$template = strtolower($className) . '/' . $template;

		self::initMenu();
		$content = self::templateBuf($template, $params);
		register_shutdown_function(function ($string) {
			echo $string;
			exit;
		}, $content);
	}

	/** render template to json */
	final public static function templateJson($template, array $params = array(), $status = 'ok')
	{
		$content = [
			'status' => $status,
			'data'   => self::templateBuf($template, $params)
		];
		register_shutdown_function(function ($data) {
			View::outJson($data);
			exit;
		}, $content);
	}

	/** render json */
	public static function outJson($data = array())
	{
		header('Content-Type: application/json');
		echo self::templateBuf('base/template', array('data' => $data));
		exit;
	}

	/**  output json with status OK and data array */
	public static function outJsonOk($data = NULL)
	{
		$json = array('status' => 'ok');
		if ($data) {
			$json = array_merge($data, $json);
		}
		self:: outJson($json);
	}


	/**  output json with status fail and data array */
	public static function outJsonFail($data = NULL)
	{
		$json = array('status' => 'fail');
		if ($data) {
			$json = array_merge($data, $json);
		}
		self:: outJson($json);
	}


	/** return render template */
	final public static function templateBuf($template, array $params = array())
	{
		// можно поменять на другой шаблнизатор, например blitz, smarty ...
		return self::renderTwig($template, $params);
	}


	/**
	 * realization template engine twig
	 *
	 * @param       $template
	 * @param array $params
	 *
	 * @return bool
	 */
	final private static function renderTwig($template, $params = array())
	{
		if (!self::$engineInstance['twig']) {
			self::$engineInstance['twig'] = new Twig_Environment(new Twig_Loader_Filesystem(DIR_TEMPLATES), array('debug' => true));
			self::$engineInstance['twig']->addExtension(new Twig_Extension_Debug());
		}
		$twig = self::$engineInstance['twig'];
		$params = array_merge(self::$params, $params);

		$type = self::hasJsonHeader() ? 'json' : 'html';

		return $twig->render($template . '.' . $type . '.twig', $params);
		//		self::$engineInstance['twig']->display($template . '.html.twig', $params);
	}

	public static function hasJsonHeader()
	{
		$json = false;
		foreach (headers_list() as $header) {
			if (preg_match('/content-type: ([^;]+)/i', $header, $match)) {
				if (strstr($match[1], 'json'))
					$json = true;
			}
		}

		return $json;
	}

	/** init menu for view content */
	public static function initMenu()
	{
		if (!self::$menu) {
			self::$menu = array();
			$menu = include DIR_CONFIG . '/menu.inc.php';
			$itemActive = Route::getRouteUri();

			foreach ($menu as $key => $value) {
				$link = '/' . $key;
				$active = false;
				if ($arr = preg_split('/\*/', $key)) {
					$link = str_replace('*', "index", $link);
					$path = str_replace('/', '\/', implode('[\s\S]+?', $arr));
					if (preg_match('/^' . $path . '$/i', $itemActive))
						$active = true;
				}

				self::$menu[$key] = array('title' => $value, 'link' => $link, 'active' => $active);
			}

			if ($menu) {
				foreach (self::$menu as $key => $value) {
					$link = str_replace('/', '\/', $key);
					if ($arr = preg_split('/\*/', $link)) {
						$link = implode('[\s\S]+?', $arr);
					}
					if (preg_match('/^' . $link . '$/i', $itemActive)){
						self::$title = $value['title'];
					}
				}
			}
			self::$pageTitle = self::$title;

			self::setGlobal(array(
				'menu'      => self::$menu,
				'title'     => self::$title,
			));
		}

	}

	final public static function getName()
	{
		if (preg_match('/^(.+)view/i', get_called_class(), $m))
			return $m[1];
	}
}