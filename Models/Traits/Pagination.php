<?php

namespace Models\Traits;

trait Pagination
{

	/**
	 * Return the serach parameters without 
	 * the pagination parameters
	 * 
	 * @param array $params
	 * @return array
	 */
	protected function filterPaginationParams($params){
	    unset($params['page']);
		unset($params['pageSize']);
		return $params;
	}

	/**
	 * @param \Ds\Deque $result_complete
	 * @param array $params
	 * @return \Ds\Deque
	 */
	protected function _getPage(\Ds\Deque $result_complete, array $params){
		if( !isset($params['page']) || !isset($params['pageSize']) )
			return $result_complete;

		$current_page = $result_complete->slice(
			($params['page'] - 1) * $params['pageSize'], 
			$params['pageSize']
		);

		return $current_page;
	}

	/**
	 * Identify if a search is to be paginated
	 * 
	 * @param array $params
	 * @return Bool
	 */
	protected function _isPaginated(array $params){
		return isset($params['pageSize']) 
			   && isset($params['pageSize']);
	}

}