<?php

/** @var Bramus\Router\Router $router */

// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/', App\Controllers\IndexController::class . '@test');
//Applying newfound knowledge of subrouting
$router->mount('/facilities', function () use ($router){
	$router->post('/', App\Controllers\DbController::class . '@createFacility');
	$router->get('/', App\Controllers\DbController::class . '@getFacility');
	$router->get('/(\d+)', App\Controllers\DbController::class . '@getFacility');
	$router->delete('/(\d+)', App\Controllers\DbController::class . '@deleteFacility');
	//TODO: Update (PATCH, maybe PUT?)
	$router->patch('/(\d+)', App\Controllers\DbController::class . '@updateFacility');
});
$router->mount('/tags', function () use ($router){
	$router->post('/', App\Controllers\DbController::class . '@createTag');
	//$router->patch('/(\d+)', App\Controllers\DbController::class . '@updateTagOnId');	
	$router->patch('/(\w+)', App\Controllers\DbController::class . '@updateTagOnName');
	$router->get('/', App\Controllers\DbController::class . '@getTag');
	$router->get('/(\d+)', App\Controllers\DbController::class . '@getTag');
	$router->delete('/(\d+)', App\Controllers\DbController::class . '@deleteTag');
});
