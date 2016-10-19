<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';

$init = new \SlothDemo\Initialisation();
$app = $init->getApp();

/**
 * @var \Sloth\Module\Router\RouterModule $routerModule
 */
$routerModule = $app->module('router');

/**
 * @var \Sloth\Module\Request\RequestModule $requestModule
 */
$requestModule = $app->module('request');

$request = $requestModule->fromServerVars();
$routedRequest = $routerModule->route($request);
$controller = $routedRequest->getController();

echo $controller->execute($routedRequest);

echo sprintf('<link rel="stylesheet" type="text/css" href="%s/public/css/theme.css">', $app->rootUrl());
