<?php

/**
 * Example URLs:
 * http://my-project.co.uk => DefaultController::getDefault
 * http://my-project.co.uk/about => DefaultController::getAbout
 * http://my-project.co.uk/contact => DefaultController::getContact
 *
 * http://my-project.co.uk/resource/recipe/1 => return recipe with ID 1 in default (i.e. HTML) format
 * http://my-project.co.uk/recipe/1 => RecipeController::getDefault. Return whole HTML page with recipe with ID 1.
 *
 * http://my-project.co.uk/resource/recipe => return all recipes in default (i.e. HTML) format
 * http://my-project.co.uk/recipe => RecipeController::getDefault. Return whole HTML page with recipe index.
 *
 * http://my-project.co.uk/resource/recipe/1.json => return recipe with ID 1 in JSON format
 * http://my-project.co.uk/recipe/1.json => RecipeController::getJson. Return recipe with ID 1 in JSON format.
 *
 * http://my-project.co.uk/resource/recipe.json => return all recipes in JSON format
 * http://my-project.co.uk/recipe.json => RecipeController::getJson. Return all recipes in JSON format.
 *
 *
 *
 * Check cache for full request URI, including query string.
 *
 * Check routes for partial or full request path.
 *
 * Check whether first element matches a controller name => that controller::execute().
 *
 * Else => DefaultController::execute(). (Or some other controller, by config)
 *
 */

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';

new Sloth\Utility\Autoload(__DIR__, 'SlothDemo');

$config = new Sloth\Demo\Config();
$request = Sloth\Request::fromServerVars();
$init = $config->initialisation();
$app = $init->getApp();
echo $init->getRouter()->route($app, $request);
