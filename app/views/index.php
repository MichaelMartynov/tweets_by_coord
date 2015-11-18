<?php

class IndexView extends View
{

	public static function main($content)
	{
		self::template('index', array(
			'title'     => 'Главная',
			'pageTitle' => 'Для любимой студии ;)',
			'content'   => $content
		));
	}

	public static function about($content)
	{
		self::template('static', array(
			'title'     => 'Об авторе',
			'pageTitle' => 'Резюме',
			'content' => $content
		));
	}
}