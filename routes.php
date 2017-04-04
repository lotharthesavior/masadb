<?php

$app->get('/', 'HomeController:home');


// NOTES ------------------------------------------------------------------------------------------

$app->get('/notes', 'NotesController:getNotes')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/notes/{id}', 'NotesController:getNote')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->post('/notes', 'NotesController:saveNote')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->put('/notes/{id}', 'NotesController:saveNote')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->delete('/notes/{id}', 'NotesController:deleteNote')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / NOTES ----------------------------------------------------------------------------------------


// USERS ------------------------------------------------------------------------------------------

$app->get('/users', 'UsersController:getUsers')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/users/{id}', 'UsersController:getUser')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->post('/users', 'UsersController:saveUser')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->put('/users/{id}', 'UsersController:saveUser')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->delete('/users/{id}', 'UsersController:delete')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / USERS ----------------------------------------------------------------------------------------


// CLIENTS ------------------------------------------------------------------------------------------

$app->get('/clients', 'ClientsController:getClients')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/clients/{id}', 'ClientsController:getClient')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->post('/clients', 'ClientsController:saveClient')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->put('/clients/{id}', 'ClientsController:saveClient')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->delete('/clients/{id}', 'ClientsController:delete')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / CLIENTS ----------------------------------------------------------------------------------------


// REPOSITORIES ------------------------------------------------------------------------------------------

$app->get('/listRepositories', 'RepositoriesController:getRepositories')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/repositories/{id}', 'RepositoriesController:getRepository')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/repositories/{id}/{asset}', 'RepositoriesController:getAsset')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / REPOSITORIES ----------------------------------------------------------------------------------------


// OAUTH2 ------------------------------------------------------------------------------------------

$app->post('/access_token', 'OAuthController:accessToken');

$app->post('/generate_key', 'OAuthController:generateClientKey');
    // ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / OAUTH2 ----------------------------------------------------------------------------------------