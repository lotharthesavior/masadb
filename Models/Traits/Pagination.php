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
	 * @param \Ds\Deque $result_complete
	 * @param Array $params
	 * @return \Ds\Deque
	 */
	protected function _getPage(\Ds\Deque $result_complete, Array $params){
		$current_page = $result_complete->slice(
			($params['page'] - 1) * $params['pageSize'], 
			$params['pageSize']
		);

		return $current_page;
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