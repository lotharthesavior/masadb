<?php

$app->get('/', 'HomeController:home');

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


// Generic Database ------------------------------------------------------------------------------------------

$app->get('/{database}', 'MasaDBController:getFullCollection')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/{database}/{id}', 'MasaDBController:getRecord')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// $app->get('/{database}/{params:.*}', 'MasaDBController:searchRecords')
$app->get('/{database}/{key}/{value}', 'MasaDBController:searchRecords')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

/**
 * @todo specify format
 */
$app->post('/{database}/search', 'MasaDBController:searchRecordsPost')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->post('/{database}', 'MasaDBController:saveGeneric')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->put('/{database}/{id}', 'MasaDBController:saveGeneric')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->delete('/{database}/{id}', 'MasaDBController:deleteNote')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / Generic Database ------------------------------------------------------------------------------------------


