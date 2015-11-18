<?php

class IndexController extends Controller
{

	public function indexAction()
	{
		$this->display('main', array(
			'content' => file_get_contents(DIR_ASSETS . '/task.html')
		));
	}

	public function aboutAction()
	{
		$html = file_get_contents('http://samara.hh.ru/resume/8f8cc4cdff02af86230039ed1f484f39334578?print=true');

		$this->display('about', array(
			'content' => $html
		));
	}

}