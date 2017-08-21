<?php

$app->get('/', 'HomeController:home');


// OAUTH2 ------------------------------------------------------------------------------------------

$app->post('/access_token', 'OAuthController:accessToken');

$app->post('/generate_key', 'OAuthController:generateClientKey');
    // ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / OAUTH2 ----------------------------------------------------------------------------------------


// ASYNC CALLS ----------------------------------------------------------------------------------------

$app->post('/git-async', 'MasaDBController:gitAsync');
    // ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->post('/update-cache-async', 'MasaDBController:updateCacheAsync');
    // ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / ASYNC CALLS ----------------------------------------------------------------------------------------


// Generic Database ------------------------------------------------------------------------------------------

$app->get('/{database}', 'MasaDBController:getFullCollection')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/{database}/{id}', 'MasaDBController:getGeneric')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->get('/{database}/{key}/{value}', 'MasaDBController:searchRecords')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->post('/{database}/search', 'MasaDBController:searchRecordsPost')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->post('/{database}', 'MasaDBController:saveGeneric')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->put('/{database}/{id}', 'MasaDBController:saveGeneric')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

$app->delete('/{database}/{id}', 'MasaDBController:deleteGeneric')
    ->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// / Generic Database ------------------------------------------------------------------------------------------

