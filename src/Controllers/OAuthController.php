<?php

namespace Controllers;

use DateInterval;
use Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Models\Repositories;
use Models\Generic;
use Models\OAuth2\Clients;
use Models\Users;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Exception\OAuthServerException;
use Zend\Diactoros\Stream;

use Repositories\ClientRepository;
use Repositories\AccessTokenRepository;
use Repositories\ScopeRepository;

class OAuthController
{

    protected $container;

    /**
     * Start the controller instantiating the Slim Container
     * @todo move this to a controller parent class
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Access Token
     *
     * Post header data example:
     *
     *     Content-Type: application/x-www-form-urlencoded
     *
     * Post body data example:
     *
     *     grant_type=client_credentials&client_id={client id}&client_secret={secret}&scope={scopes list}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function accessToken(ServerRequestInterface $request, ResponseInterface $response)
    {
        // Path to public and private keys
        $privateKey = new CryptKey(
            $this->container->get('settings')['private_key'],
            $this->container->get('settings')['public_key_pass']
        ); // if private key has a pass phrase
        $publicKey = $this->container->get('settings')['public_key'];

        $client_repository = $this->container->get(ClientRepository::class);

        $access_token_repository = $this->container->get(AccessTokenRepository::class);

        $scope_repository = $this->container->get(ScopeRepository::class);

        /* @var \League\OAuth2\Server\AuthorizationServer $server */
        $server = new AuthorizationServer(
            $client_repository,
            $access_token_repository,
            $scope_repository,
            $privateKey,
            $publicKey
        );
        $server->setEncryptionKey('lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIensavio');

        // Enable the client credentials grant on the server
        $server->enableGrantType(
            new ClientCredentialsGrant(),
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        try {

            // Try to respond to the request
            return $server->respondToAccessTokenRequest($request, $response);

        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);

        } catch (Exception $exception) {

            // Unknown exception
            $body = new Stream('php://temp', 'r+');
            $body->write($exception->getMessage());
            return $response->withStatus(500)->withBody($body);

        }
    }

    /**
     * Expected Body Format:
     *
     *     {
     *         "email": string
     *         "password": string
     *     }
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function generateClientKey(ServerRequestInterface $request, ResponseInterface $response)
    {
        $secret_key = uniqid();

        $client = $request->getParam('client');
        $email = $request->getParam('email');
        $password = $request->getParam('password');

        // find client
        $clients_model = $this->container->get(Clients::class);
        $client_result = $clients_model->find($client);

        // find user
        $users_model = $this->container->get(User::class);
        $users_result = $users_model->find($client_result->file_content->user_id);

        // validate user credential

        if (
            $users_result->file_content->email != $email
            || $users_result->file_content->password != $password
        ) {
            return false;
        }

        // update client with secret

        $client_result->file_content->secret_key = sha1($secret_key);

        $new_client_data = (array) $client_result->file_content;

        return $clients_model->save([
            'id' => $client,
            'content' => $new_client_data
        ]);
    }

}
