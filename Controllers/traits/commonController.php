<?php

namespace Controllers\traits;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait commonController
{

	/**
	 * Expected Body Format: 
	 * 	{
	 * 		"title": {string},
	 * 		"author": {string},
	 * 		"email": {string},
	 * 		"content": {string}
	 * 	}
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param Array $args
	 * @param \Models\GitModel $model
	 */
	public function saveRecord(ServerRequestInterface $request, ResponseInterface $response, array $args, \Models\GitModel &$model){

	 	// request data
		
		$request_body = json_decode($request->getBody(), true);

		// handling put data
		if( is_null($request_body) ){
			$rawData = file_get_contents('php://input');
		 	$rawData = mb_substr($rawData, 3, -3);
			parse_str($rawData, $request_body);
		}

		$id = null;
		if( isset($args['id']) ){
			$id = $args['id'];
		}

		// model interation

		try {

			$client_data = array_merge(["id" => $id, "content" => $request_body]);

			$message = $model->save( $client_data );
			
			$result = [
				"Success"        => 1,
				"SuccessMessage" => $message
			];

		} catch (\Exception $e) {
			
			$result = [
				"Error"        => 1, 
				"ErrorMessage" => $e->getMessage()
			];

		}

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

	/**
	 * 
	 */
	protected function deleteRecord(ServerRequestInterface $request, ResponseInterface $response, array $args, \Models\GitModel $model){

		try {

			$message = $model->delete( $args['id'] );
			
			$result = [
				"Success"        => 1,
				"SuccessMessage" => $message
			];

		} catch (\Exception $e) {
			
			$result = [
				"Error"        => 1, 
				"ErrorMessage" => $e->getMessage()
			];

		}

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

	/**
	 * Organinize unlimited params with the Slimframework Router
	 * 
	 * @param String $params
	 */
	protected function processUnlimitedParams( $params ){

		$param  = [];
		$values = [];

		$params = explode("/", $params['params']);


		foreach ($params as $key => $value) {
			if ($key % 2 == 0) {
				array_push($param, $value);
			}else{
				array_push($values, $value);
			}
		}

		return [
			'field' => $param,
			'value' => $values
		];

	}

}