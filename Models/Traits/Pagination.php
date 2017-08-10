<?php

namespace Models\Traits;

trait Pagination
{

	/**
	 * Return the serach parameters without 
	 * the pagination parameters
	 * 
	 * @param Array $params
	 * @return Array
	 */
	protected function filterPaginationParams($params){
		unset($params['page']);
		unset($params['pageSize']);
		return $params;
	}

	/**
	 * @param \Ds\Vector $result_complete
	 * @param Array $params
	 * @return Array [\Ds\Vector, Array]
	 */
	protected function _preparePages(\Ds\Vector $result_complete, Array $params){
		$result_paginated = [
			'results' => new \Ds\Vector,
			'pages' => new \Ds\Vector
		];

		$number_of_pages = ceil($result_complete->count()/$params['pageSize']);

		for ($i=0; $i < $number_of_pages; $i++) {
			$result_paginated['results']->push( $result_complete->slice($i * $params['pageSize'], $params['pageSize']) );
			$result_paginated['pages']->push( $i + 1 );
		}

		return $result_paginated;
	}

	/**
	 * Identify if a search is to be paginated
	 * 
	 * @param Array $params
	 * @return Bool
	 */
	protected function _isPaginated(Array $params){
		return isset($params['pageSize']) 
			   && isset($params['pageSize']);
	}

}