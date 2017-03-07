<?php

date_default_timezone_set('UTC');

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$init = new \Example\ToDoList\AppInitialisation();
$app = $init->getApp();

// Initialise a session
$sessionModule = $app->module('session');

/**
 * @var \Celestial\Module\Log\LogModule $logModule
 */
$logModule = $app->module('log');

$logger = $logModule->createLogger(__FILE__);

/**
 * @var \Celestial\Module\Router\RouterModule $routerModule
 */
$routerModule = $app->module('router');

/**
 * @var \Celestial\Module\Request\RequestModule $requestModule
 */
$requestModule = $app->module('request');

$request = $requestModule->fromServerVars();

$routedRequest = $routerModule->route($request);
$controller = $routedRequest->getController();

$logger->info('Executing request', [
	'request' => $routedRequest->toArray(),
	'controller' => get_class($controller)
]);

$response = $controller->execute($routedRequest);

$logger->info('Responding', ['response' => $response]);

echo $response;
