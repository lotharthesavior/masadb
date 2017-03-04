<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Notes;

class HomeController
{

	/**
	 * 
	 */
	public function __construct(){
		
	}

	/**
	 * 
	 */
	public function home(ServerRequestInterface $request, ResponseInterface $response){

		$response->getBody()->write( "masa git repository" );

    	return $response;

	}

}