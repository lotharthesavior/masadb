<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Repositories;
use \Models\Generic;
use \Models\OAuth2\Clients;
use \Models\Users;

use League\OAuth2\Server\AuthorizationServer;

class OAuthController
{

	/**
	 * Access Token
	 * 
	 * Example: grant_type=client_credentials&client_id={client id}&client_secret={secret}&scope={scopes list}
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	public function accessToken(ServerRequestInterface $request, ResponseInterface $response){

		// Path to public and private keys
		$privateKey = '/home/vagrant/Code/gitdev/private.key';
		//$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
		$publicKey = '/home/vagrant/Code/gitdev/public.key';

		$client_repository = new \Repositories\ClientRepository;

		$access_token_repository = new \Repositories\AccessTokenRepository;

		$scope_repository = new \Repositories\ScopeRepository;

		/* @var \League\OAuth2\Server\AuthorizationServer $server */
	    // $server = AuthorizationServer::class;
	    $server = new AuthorizationServer(
	    	$client_repository,
	    	$access_token_repository,
	    	$scope_repository,
	    	$privateKey,
	        $publicKey
	    );

	    // Enable the client credentials grant on the server
		$server->enableGrantType(
		    new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
		    new \DateInterval('PT1H') // access tokens will expire after 1 hour
		);

	    try {
	    
	        // Try to respond to the request
	        return $server->respondToAccessTokenRequest($request, $response);
	        
	    } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
	    
	        // All instances of OAuthServerException can be formatted into a HTTP response
	        return $exception->generateHttpResponse($response);
	        
	    } catch (\Exception $exception) {
	    
	        // Unknown exception
	        $body = new \Zend\Diactoros\Stream('php://temp', 'r+');
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
	  * @return Boolean
	  */
	public function generateClientKey(ServerRequestInterface $request, ResponseInterface $response){

		$secret_key = uniqid();

		$client = $request->getParam('client');
		$email = $request->getParam('email');
		$password = $request->getParam('password');

		// find client 

		$clients_model = new Clients;
		$client_result = $clients_model->find( $client );

		// find user

		$users_model = new Users;
		$users_result = $users_model->find( $client_result->file_content->user_id );
		
		// validate user credential

		if(
			$users_result->file_content->email != $email
			|| $users_result->file_content->password != $password
		){
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