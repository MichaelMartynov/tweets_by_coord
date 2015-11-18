<?php

// check - installation composer
is_dir('vendor') or die('please initial project (README.md)');

// set utf8 encode for mb_ functions
mb_internal_encoding("UTF-8");

// define project folders
define ('DIR_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
define ('DIR_LOGS', DIR_ROOT . '/logs');
define ('DIR_ASSETS', DIR_ROOT . '/assets');
define ('DIR_PROTECTED', DIR_ROOT . '/protected');
define ('DIR_TEMPLATES', DIR_ROOT . '/templates');
define ('DIR_APP', DIR_ROOT . '/app');
define ('DIR_CONFIG', DIR_APP . '/config');
define ('DIR_CONTROLLERS', DIR_APP . '/controllers');
define ('DIR_MODELS', DIR_APP . '/models');
define ('DIR_VIEWS', DIR_APP . '/views');
define ('DIR_DB', DIR_APP . '/databases');

// autoload composer libs
require_once("vendor/autoload.php");

// shot functions for $_SERVER, array, sessions ...
require_once(DIR_PROTECTED . '/utils.php');

// autoload project
spl_autoload_register('_classAutoload');

function _classAutoload($class)
{
	$dir = NULL;
	if (file_exists(DIR_PROTECTED . '/' . strtolower($class) . '.php')) {
		// found in protected dir
		$name = $class;
		$dir = DIR_PROTECTED;
	} elseif (file_exists(DIR_APP . '/' . strtolower($class) . '.php')) {
		// found in app dir
		$name = $class;
		$dir = DIR_APP;
	} else {
		if (preg_match('/^(.+)controller$/i', $class, $m)) {
			$dir = DIR_CONTROLLERS;
		} elseif (preg_match('/^(.+)model$/i', $class, $m)) {
			$dir = DIR_MODELS;
		} elseif (preg_match('/^(.+)view$/i', $class, $m)) {
			$dir = DIR_VIEWS;
		}
		empty($m) || $name = $m[1];
	}

	if ($dir) {
		// replace '_'->'/' for find classes in sub folders
		// example: Module_IndexController -> /app/controllers/module/index.php
		$file = "/" . str_replace('_', '/', strtolower($name)) . ".php";
		if (file_exists($dir . $file))
			require_once($dir . $file);
		else throw new Exception("unable to load `$class` class.");
	} else throw new Exception("unable to load `$class` class.");
}

// register errors and exceptions
Debug::init();

Db::connect("sqlite:" . DIR_DB . "/main.db");