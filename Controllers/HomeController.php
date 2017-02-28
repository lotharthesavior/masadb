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

		global $templates;

	    return $templates->render('index');

	}

}