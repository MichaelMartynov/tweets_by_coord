<?php

class TwitterModel extends Model
{

	public static function getTable()
	{
		return 'twitter';
	}

	public static function describe($full = false)
	{
		$fields = array(
			'latitude'  => 'TEXT',
			'longitude' => 'TEXT',
			'response'  => 'TEXT'
		);

		array_walk($fields, function(&$val,$key){
			$val = "$key $val";
		});

		return $full ? $fields : array_keys($fields);
	}

}