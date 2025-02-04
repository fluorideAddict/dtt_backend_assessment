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

//$router->get('/readTag/(\w+)', App\Controllers\DbController::class . '@readTag');
//For some reason, $router->post results in the error {"error":"Route not defined"}, so I am using exclusively get requests.
//This is not ideal, however I'm struggling to figure out an alternative.
//createFacility/NameOfFacilityHere/
//$router->get('/createFacility/(\w+)', App\Controllers\DbController::class . '@createFacility');
//$router->post('/createTag/(\w+)', App\Controllers\DbController::class . '@createTag');
