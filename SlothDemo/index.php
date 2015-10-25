<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

new Sloth\Utility\Autoload(__DIR__, 'SlothDemo');

$init = new \SlothDemo\Initialisation();

/**
 * @var \Sloth\App $app
 */
$app = $init->getApp();

/**
 * @var \Sloth\Module\Router\RouterModule $router
 */
$router = $app->module('router');

/**
 * @var \Sloth\Module\Request\RequestModule $request
 */
$request = $app->module('request');

echo $router->route($request->fromServerVars());