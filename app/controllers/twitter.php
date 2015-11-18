<?php

class TwitterController extends ControllerCrud
{

	/**
	 * get model name for simple crud
	 * @return string
	 */
	public static function getModelName()
	{
		return 'Twitter';
	}

	protected function generateObject()
	{
		return array(
			'latitude'  => rand(-90000, 90000)/1000, // долгота
			'longitude' => rand(-180000, 180000)/1000, // широта
			'response'  => 'abc'
		);
	}


}