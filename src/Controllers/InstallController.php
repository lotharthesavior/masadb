<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\Plates\Engine;

class InstallController extends Abstraction\MasaController
{

	protected $container;

    /**
     * Start the controller instantiating the Slim Container
     * 
     * @todo move this to a controller parent class
     */
    public function __construct($container){
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
	public function index(ServerRequestInterface $request, ResponseInterface $response)
	{
		// $templates = new League\Plates\Engine('');
	}
}