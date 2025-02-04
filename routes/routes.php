<?php

/** @var Bramus\Router\Router $router */

// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/', App\Controllers\IndexController::class . '@test');
//Applying newfound knowledge of subrouting
$router->mount('/facility', function () use ($router){
	$router->post('/', App\Controllers\DbController::class . '@createFacility');
	$router->get('/(\w+)', App\Controllers\DbController::class . '@readFacility');
});
$router->mount('/tags', function () use ($router){
	$router->get('/(\w+)', App\Controllers\DbController::class . '@createTag');
});
