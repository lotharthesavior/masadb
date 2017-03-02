<?php

$route = new League\Route\RouteCollection($container);

$route->map('GET', '/', '\Controllers\HomeController::home');


// NOTES ------------------------------------------------------------------------------------------

$route->map('GET', '/notes', '\Controllers\NotesController::getNotes');

$route->map('GET', '/notes/{id}', '\Controllers\NotesController::getNote');

$route->map('POST', '/notes', '\Controllers\NotesController::saveNote');

$route->map('PUT', '/notes/{id}', '\Controllers\NotesController::saveNote');

$route->map('DELETE', '/notes/{id}', '\Controllers\NotesController::delete');

// / NOTES ----------------------------------------------------------------------------------------


// REPOSITORIES ------------------------------------------------------------------------------------------

$route->map('GET', '/repositories', '\Controllers\RepositoriesController::getRepositories');

$route->map('GET', '/repositories/{id}', '\Controllers\RepositoriesController::getRepository');

$route->map('GET', '/repositories/{id}/{asset}', '\Controllers\RepositoriesController::getAsset');

// / REPOSITORIES ----------------------------------------------------------------------------------------


// OAUTH2 ------------------------------------------------------------------------------------------

$route->map('POST', '/access_token', '\Controllers\OAuthController::accessToken');

// / OAUTH2 ----------------------------------------------------------------------------------------