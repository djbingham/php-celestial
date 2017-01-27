<?php

date_default_timezone_set('UTC');

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';

$init = new \SlothDemo\Initialisation();
$app = $init->getApp();

// Initialise a session
$sessionModule = $app->module('session');

/**
 * @var \Sloth\Module\Log\LogModule $logModule
 */
$logModule = $app->module('log');

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

$logModule->logInfo(sprintf('Executing request using controller `%s`', get_class($controller)), $routedRequest->toArray());

echo $controller->execute($routedRequest);

echo sprintf('<link rel="stylesheet" type="text/css" href="%s/public/css/theme.css">', $app->rootUrl());
