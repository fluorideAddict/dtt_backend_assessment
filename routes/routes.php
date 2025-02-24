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
	$router->delete('/(\d+)', App\Controllers\MainController::class . '@deleteFacility');
	$router->patch('/(\d+)', App\Controllers\MainController::class . '@updateFacility');
});
$router->mount('/tags', function () use ($router){
	$router->post('/', App\Controllers\MainController::class . '@createTag');
	//The router does not differentiate between digit and word character matches, therefore it will require a specialised regular expression to be able to tell the two apart. For now this functionality has been left out as the Name column of the Tags table is unique and searching for tags by name would be the more common use case as the Tags of a returned Facility object is comprised solely of Tag names
	//$router->patch('/(\d+)', App\Controllers\DbController::class . '@updateTagOnId');	
	$router->patch('/(\w+)', App\Controllers\MainController::class . '@updateTagOnName');
	$router->get('/', App\Controllers\MainController::class . '@getTag');
	$router->get('/(\d+)', App\Controllers\MainController::class . '@getTag');
	$router->delete('/(\d+)', App\Controllers\MainController::class . '@deleteTag');
});
