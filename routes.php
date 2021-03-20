<?php

use \League\OAuth2\Server\Middleware\ResourceServerMiddleware;

$app->get('/', 'HomeController:home');

// ------------------------------------------------------------------------------------------
// OAUTH2
// ------------------------------------------------------------------------------------------

$app->post('/access_token', 'OAuthController:accessToken');

$app->post('/generate_key', 'OAuthController:generateClientKey');

// ----------------------------------------------------------------------------------------
// / OAUTH2
// ----------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------
// Generic Database
// ------------------------------------------------------------------------------------------

$oauthMiddleware = function ($request, $response, $next) use ($config, $server) {
    if ($config['settings']['env'] === APP_ENV_PROD) {
        return (new ResourceServerMiddleware($server))($request, $response, $next);
    }

    return $next($request, $response);
};

$app->get('/{database}', 'MasaDBController:getFullCollection')
    ->add($oauthMiddleware);

$app->get('/{database}/{id:[0-9]+}', 'MasaDBController:getGeneric')
    ->add($oauthMiddleware);

$app->get('/{database}/file', 'MasaDBController:getGenericFile')
    ->add($oauthMiddleware);

$app->get('/{database}/{key}/{value}', 'MasaDBController:searchRecords')
    ->add($oauthMiddleware);

$app->post('/{database}/search', 'MasaDBController:searchRecordsPost')
    ->add($oauthMiddleware);

$app->post('/{database}', 'MasaDBController:saveGeneric')
    ->add($oauthMiddleware);

$app->put('/{database}/{id:[0-9]+}', 'MasaDBController:saveGeneric')
    ->add($oauthMiddleware);

$app->put('/{database}/file', 'MasaDBController:saveGeneric')
    ->add($oauthMiddleware);

$app->delete('/{database}/{id:[0-9]+}', 'MasaDBController:deleteGeneric')
    ->add($oauthMiddleware);

$app->delete('/{database}/file', 'MasaDBController:deleteGenericFile')
    ->add($oauthMiddleware);

// TODO: upload asset

// ------------------------------------------------------------------------------------------
// Git Procedures
// ------------------------------------------------------------------------------------------

// ----COMMITS----

// TODO: Stage Commit

// TODO: Create Commit

// TODO: Revert Commit

// TODO: Amend Commit

// ----LOGS---

// TODO: Show X Logs

// TODO: Search Logs

// ----REMOTES----

// TODO: Add Remote

// TODO: Remove Remote

// TODO: List Remote

// TODO: Find Remote

// ----BRANCHES----

// TODO: Add Branch

// TODO: Remove Branch

// TODO: Remove Remote Branch

// TODO: Push to Remote Branch

// TODO: Pull from Remote Branch

// TODO: Move to Branch (checkout)

// TODO: Merge Branch

// TODO: Get Current Branch

// TODO: Find Branch

// ----TAGS----

// TODO: List Tags

// TODO: Search Tags

// TODO: Create Tags

// TODO: Show Tags

// TODO: Add Tag to Commit

// TODO: Push Tag to Remote

// TODO: Move to Tag (checkout)

// ----CONFIGS----

// TODO: set automatic commits: false or true

// TODO: set automatic push remotes: false or true

// TODO: set to ignore chmod changes: false or true

// ------------------------------------------------------------------------------------------
// / Git Procedures
// ------------------------------------------------------------------------------------------
