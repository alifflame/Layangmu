<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/users/register', 'AuthController@register');
$router->post('/users/login', 'AuthController@login');
$router->post('/users/logout', 'AuthController@logout');

$router->get('/users/profile', 'UsersController@profile');
$router->get('/users/{id}', 'UsersController@singleUser');
$router->get('/users', 'UsersController@allUsers');

$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    $router->get('/api/documents', 'DocumentController@index');
    $router->post('/api/documents/delete', 'DocumentController@multipleDelete');
    $router->post('/api/documents/archive', 'DocumentController@multipleArchive');
    $router->post('/api/documents/unarchive', 'DocumentController@multipleUnarchive');
    $router->delete('/api/documents/{id}', 'DocumentController@delete');    
    $router->get('/api/documents/{id}', 'DocumentController@show');
    $router->post('/api/documents/{id}/archive', 'DocumentController@archive');
    $router->post('/api/documents/{id}/unarchive', 'DocumentController@unarchive');
    $router->get('/api/documents/{id}/file', 'DocumentController@file');
    $router->post('/api/documents/', 'DocumentController@store');
    $router->put('/api/documents/{id}', 'DocumentController@update');
});
