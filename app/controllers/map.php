<?php

use Abraham\TwitterOAuth\TwitterOAuth;

class MapController extends Controller
{

	public function indexAction()
	{
		$this->display('index');
	}


	public function searchAction($latitude, $longitude, $radius = '0.5km', $count = 10)
	{

		$settings = include DIR_CONFIG . '/twitter.inc.php';

		$connection = new TwitterOAuth($settings['consumer_key'], $settings['consumer_secret'], $settings['oauth_access_token'], $settings['oauth_access_token_secret']);
		$method = 'search/tweets';
		$params = array(
			"q"           => "photo",
			"result_type" => "recent",
			"geocode"     => implode(',', array($latitude, $longitude, $radius)),
			"count"       => $count
		);

		$response = $connection->get($method, $params);
		if ($connection->getLastHttpCode() != 200) {
			View::outJsonFail(array('message' => 'Twitter error'));
		}

		$tweet = new TwitterModel(array(
			'latitude'  => $latitude,
			'longitude' => $longitude,
			'response'  => json_encode($response->statuses)
		));
		$tweet->create();

		$url = NULL;
		$i = 0;
		if (count($response->statuses)) {
			while (isset($response->statuses[$i]) && !$url) {

				$entities = $response->statuses[$i++]->entities;
				if (isset($entities->media))
					$url = $entities->media[0]->media_url;
			}
		}

		View::outJsonOk(array(
			'image'     => $url ?: '/assets/img/Error-404.jpg',
			'latitude'  => $url ? $response->statuses[$i - 1]->geo->coordinates[0] : NULL,
			'longitude' => $url ? $response->statuses[$i - 1]->geo->coordinates[1] : NULL,
			'response'  => $response
		));

	}


}