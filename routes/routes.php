<?php

/** @var Bramus\Router\Router $router */

// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/', App\Controllers\IndexController::class . '@test');
//Applying newfound knowledge of subrouting
$router->mount('/facilities', function () use ($router){
	$router->post('/', App\Controllers\MainController::class . '@createFacility');
	$router->get('/', App\Controllers\MainController::class . '@getFacility');
	$router->get('/(\d+)', App\Controllers\MainController::class . '@getFacility');
	$router->patch('/(\d+)', App\Controllers\MainController::class . '@updateFacility');
	$router->delete('/(\d+)', App\Controllers\MainController::class . '@deleteFacility');
	$router->delete('/(\d+)/tags/(\d+)', App\Controllers\MainController::class . '@deleteTagFromFacility');
});
$router->mount('/tags', function () use ($router){
	$router->post('/', App\Controllers\MainController::class . '@createTag');
	$router->get('/', App\Controllers\MainController::class . '@getTag');
	$router->get('/(\d+)', App\Controllers\MainController::class . '@getTag');
	$router->patch('/(\d+)', App\Controllers\MainController::class . '@updateTagOnId');	
	//The functionality below is present but unused, so the route stays disabled
	//$router->patch('/(\w+)', App\Controllers\MainController::class . '@updateTagOnName');
	$router->delete('/(\d+)', App\Controllers\MainController::class . '@deleteTag');
});

