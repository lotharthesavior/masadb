<?php

$app->get('/', 'HomeController:home');


// NOTES ------------------------------------------------------------------------------------------

$app->get('/notes', 'NotesController:getNotes');

$app->get('/notes/{id}', 'NotesController:getNote');

$app->post('/notes', 'NotesController:saveNote');

$app->put('/notes/{id}', 'NotesController:saveNote');

$app->delete('/notes/{id}', 'NotesController:delete');

// / NOTES ----------------------------------------------------------------------------------------


// USERS ------------------------------------------------------------------------------------------

$app->get('/users', 'UsersController:getUsers');

$app->get('/users/{id}', 'UsersController:getUser');

$app->post('/users', 'UsersController:saveUser');

$app->put('/users/{id}', 'UsersController:saveUser');

$app->delete('/users/{id}', 'UsersController:delete');

// / USERS ----------------------------------------------------------------------------------------


// CLIENTS ------------------------------------------------------------------------------------------

$app->get('/clients', 'ClientsController:getClients');

$app->get('/clients/{id}', 'ClientsController:getClient');

$app->post('/clients', 'ClientsController:saveClient');

$app->put('/clients/{id}', 'ClientsController:saveClient');

$app->delete('/clients/{id}', 'ClientsController:delete');

// / CLIENTS ----------------------------------------------------------------------------------------


// REPOSITORIES ------------------------------------------------------------------------------------------

$app->get('/listRepositories', 'RepositoriesController:getRepositories');

$app->get('/repositories/{id}', 'RepositoriesController:getRepository');

$app->get('/repositories/{id}/{asset}', 'RepositoriesController:getAsset');

// / REPOSITORIES ----------------------------------------------------------------------------------------


// OAUTH2 ------------------------------------------------------------------------------------------

$app->post('/access_token', 'OAuthController:accessToken');

// / OAUTH2 ----------------------------------------------------------------------------------------