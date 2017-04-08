<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Repositories;
use \Models\Generic;

/**
 * 
 * Repositories Controller
 * 
 * @author Savio Resende <savio@savioresende.com.br>
 * 
 * Custom behaviour from normal Controllers:
 * 
 *     1. getRepository - list assets kept in version control
 *     2. getAsset - get the content of an specific asset in specific version in specific branch
 * 
 */

class RepositoriesController extends MasaController
{

	use \Controllers\traits\commonController;

	protected $container;

        /**
         * Start the controller instantiating the Slim Container
         * @todo move this to a controller parent class
         */
        public function __construct($container){
                $this->container = $container;
        }

	/**
	 * Get All Repositories
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	public function getRepositories(ServerRequestInterface $request, ResponseInterface $response){

		$repositories_model = new Repositories();

		$result = $repositories_model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

	/**
	 * Get Repository list of assets in it's original address
	 * 
	 * Ex.: http://{domain}/repositories/{id}
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param Array $args
	 * @return ResponseInterface
	 */
	public function getRepository(ServerRequestInterface $request, ResponseInterface $response, array $args){

		// repsitories model 

		$repositories_model = new Repositories();

		$repository = $repositories_model->find( $args['id'] );

		// generic model 

		$generic_model = new Generic();

		$generic_model->setRepo( $repository->file_content->address );

		// parsing results

		$result = $generic_model->lsTreeHead();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

	/**
	 * Get Specific Asset content
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param Array $args
	 * @return String
	 */
	public function getAsset(ServerRequestInterface $request, ResponseInterface $response, array $args){

		// repositories model 

		$repositories_model = new Repositories();

		$repository = $repositories_model->find( $args['id'] );

		// generic model 

		$generic_model = new Generic();

		$generic_model->setRepo( $repository->file_content->address );

		// parsing results

		$assets_list = $generic_model->lsTreeHead();

		$asset = $assets_list[ $args['asset'] ]->address;

		$result = $generic_model->showFile( $asset );

		return $result;

	}

}
