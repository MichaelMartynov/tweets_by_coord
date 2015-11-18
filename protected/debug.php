<?php

class Debug
{

	static function init()
	{
		ini_set('display_errors', true);
		error_reporting(E_ALL);
		set_exception_handler([__CLASS__, '_exceptionHandler']);
		set_error_handler([__CLASS__, '_errorHandler']);
		register_shutdown_function([__CLASS__, '_shutdownHandler']);
	}

	static function _exceptionHandler(Exception $ex)
	{
		self::_errorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTrace());
	}

	static function _errorHandler($errno, $errstr, $errfile, $errline, $errcontext = NULL)
	{
		$json = false;
		foreach (headers_list() as $header) {
			if (preg_match('/content-type: ([^;]+)?;/i', $header, $match))
				if (strstr($match[1], 'json'))
					$json = true;
		}

		if ($json) {
			View::outJson([
				'status'  => 'fail',
				'code'    => $errno,
				'message' => "$errstr ($errfile : $errline)",
				'trace'   => outvar($errcontext)
			]);
		} else {
			View::template('errors/500', [
				'code'    => $errno,
				'message' => "$errstr ($errfile : $errline)",
				'trace'   => outvar($errcontext, 1)
			]);
		}
		exit;
	}

	static function _shutdownHandler()
	{
		$err = error_get_last();
		if ($err) {
			$err && self::_errorHandler(-1, $err['message'], $err['file'], $err['line']);
			// not show View
			exit;
		}
	}

}

function outvar($var, $return = false)
{
	$start = "<span style='margin-left:10px; display: inline-block; vertical-align: top;'>\n";
	$end = "</span>\n";
	$EOL = "<br>\n\t";
	if (is_array($var)) {
		$content = "";
		foreach ($var as $i => $v) {
			$val = outvar($v, 1);
			$content .= $start . '<span style="color: #7F7F7F">' . $i . '</span>: ' . $val . $end . $EOL;
		}
		$content = "<span style=\"color: #0B78B9;\">array</span> [" . $EOL . $content . "]";
	} elseif (is_object($var)) {
		if (method_exists($var, '__toString')) {
			$content = '<span style="color: #356191;">' . get_class($var) . ' { ' . $var->__toString() . " }</span>";
		} else {
			$content = "";
			if (strpos(get_class($var), 'Twig') == -1) {
				foreach ((array)$var as $i => $v) {
					$val = outvar($v, 1);
					$content .= "  " . $start . '<span style="color: #7F7F7F">' . $i . '</span>: ' . $val . $end . $EOL;
				}
				$content = "<span style=\"color: #0B78B9;\">" . get_class($var) . "</span> {" . $EOL . $content . "}";
			};
		}

	} elseif (is_int($var)) {
		$content = '<span style="color: #EA0058">' . $var . '</span>';
	} elseif (is_float($var)) {
		$content = '<span style="color: #FF090F">' . $var . '</span>';
	} elseif (is_bool($var)) {
		$content = '<span style="color: #FF090F">' . ($var ? 'true' : 'false') . '</span>';
	} elseif (is_null($var)) {
		$content = '<span style="color: #FF090F">null</span>';
	} else {
		$content = '<span style="color: #379556">"' . $var . '"</span>';
	}

	if ($return)
		return $start . $content . $end;
	echo $start . $content . $end;
}