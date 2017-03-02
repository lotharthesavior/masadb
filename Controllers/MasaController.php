<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Repositories;
use \Models\Generic;

abstract class MasaController
{

	/**
	 * 
	 */
	public function __construct(){}

	/**
	 * 
	 */
	public function getAll(ServerRequestInterface $request, ResponseInterface $response){

		

	}

	/**
	 * 
	 */
	public function getOne(ServerRequestInterface $request, ResponseInterface $response){
		
	}

	/**
	 * 
	 */
	public function create(ServerRequestInterface $request, ResponseInterface $response){

	}

	/**
	 * 
	 */
	public function update(ServerRequestInterface $request, ResponseInterface $response){

	}

	/**
	 * 
	 */
	public function delete(ServerRequestInterface $request, ResponseInterface $response){

	}

}