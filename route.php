<?php

$route = new League\Route\RouteCollection($container);

$route->map('GET', '/', '\Controllers\HomeController::home');

$route->map('GET', '/notes', '\Controllers\NotesController::getNotes');

$route->map('GET', '/repositories', '\Controllers\RepositoriesController::getRepositories');

$route->map('GET', '/repositories/{id}', '\Controllers\RepositoriesController::getRepository');

$route->map('GET', '/repositories/{id}/{asset}', '\Controllers\RepositoriesController::getAsset');