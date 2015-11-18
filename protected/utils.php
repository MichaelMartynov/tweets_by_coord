<?php



function _array($array, $key, $default = NULL)
{
	return array_key_exists($key, $array) ? $array[$key] : $default;
}
