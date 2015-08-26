<?php
namespace Sloth\Demo\Controller;

use Sloth\Controller\ActionController;
use Sloth\Request;

class DefaultController extends ActionController
{
	public function actionIndex(Request $request)
	{
		$this->render('default/index');
	}
}
