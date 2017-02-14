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
	public function __construct(){}

	/**
	 * 
	 */
	public function home(ServerRequestInterface $request, ResponseInterface $response){

		global $templates;

	    $notes_model = new Notes();

		// $result = $notes_model->findAll();
		// $result = $notes_model->find(1);

		return $templates->render('index', ['repository_data' => $result]);

	}

}