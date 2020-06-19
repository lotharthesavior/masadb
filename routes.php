<?php

use \League\OAuth2\Server\Middleware\ResourceServerMiddleware;

$app->get('/', 'HomeController:home');


// OAUTH2 ------------------------------------------------------------------------------------------

$app->post('/access_token', 'OAuthController:accessToken');

$app->post('/generate_key', 'OAuthController:generateClientKey');

// / OAUTH2 ----------------------------------------------------------------------------------------


// ASYNC CALLS ----------------------------------------------------------------------------------------

// $app->post('/git-async', 'MasaDBController:gitAsync');
//     ->add(new ResourceServerMiddleware($server));

// $app->post('/update-cache-async', 'MasaDBController:updateCacheAsync');
//     ->add(new ResourceServerMiddleware($server));

// / ASYNC CALLS ----------------------------------------------------------------------------------------


// Generic Database ------------------------------------------------------------------------------------------

$oauthMiddleware = function ($request, $response, $next) use ($config) {
    if ($config['settings']['env'] === APP_ENV_PROD) {
        return (new ResourceServerMiddleware($server))($request, $response, $next);
    }

    return $next($request, $response);
};

$app->get('/{database}', 'MasaDBController:getFullCollection')
    ->add($oauthMiddleware);

$app->get('/{database}/{id}', 'MasaDBController:getGeneric')
    ->add($oauthMiddleware);

$app->get('/{database}/{key}/{value}', 'MasaDBController:searchRecords')
    ->add($oauthMiddleware);

$app->post('/{database}/search', 'MasaDBController:searchRecordsPost')
    ->add($oauthMiddleware);

$app->post('/{database}', 'MasaDBController:saveGeneric')
    ->add($oauthMiddleware);

$app->put('/{database}/{id}', 'MasaDBController:saveGeneric')
    ->add($oauthMiddleware);

$app->delete('/{database}/{id}', 'MasaDBController:deleteGeneric')
    ->add($oauthMiddleware);

// / Generic Database ------------------------------------------------------------------------------------------

