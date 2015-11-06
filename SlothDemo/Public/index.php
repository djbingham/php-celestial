<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';

new Sloth\Utility\Autoload(dirname(__DIR__), 'SlothDemo');

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

echo '<link rel="stylesheet" href="public/css/theme.css">';
