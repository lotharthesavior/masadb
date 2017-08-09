<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Notes;

class HomeController extends Abstraction\MasaController
{

	protected $container;

    /**
     * Start the controller instantiating the Slim Container
     * @todo move this to a controller parent class
     */
    public function __construct($container){
            $this->container = $container;
    }

	/**
	 * 
	 */
	public function home(ServerRequestInterface $request, ResponseInterface $response){
		
		$response->getBody()->write( "masa git repository" );

    	return $response;

	}

}
