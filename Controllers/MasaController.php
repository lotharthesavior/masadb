<?php 

namespace Controllers;

use \Repositories\AccessTokenRepository;

abstract class MasaController
{

	/**
	 * 
	 */
	protected function oauthBefore(){

		// Init our repositories
		$accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

		// Path to authorization server's public key
		$publicKey = '/home/vagrant/Code/gitdev/public.key';
		        
		// Setup the authorization server
		$server = new \League\OAuth2\Server\ResourceServer(
		    $accessTokenRepository,
		    $publicKey
		);

		new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server);

	}
	
}