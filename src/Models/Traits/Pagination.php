<?php

namespace Models\Traits;

use Ds\Deque;
use Ds\Sequence;

trait Pagination
{

    /**
     * Return the search parameters without
     * the pagination parameters
     *
     * @param array $params
     *
     * @return array
     */
    protected function filterPaginationParams(array $params): array
    {
        unset($params['page']);
        unset($params['pageSize']);
        return $params;
    }

    /**
     * @param Deque $result_complete
     * @param array $params
     *
     * @return Sequence
     */
    protected function _getPage(Deque $result_complete, array $params): Sequence
    {
        if (!isset($params['page']) || !isset($params['pageSize']))
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
     *
     * @return bool
     */
    protected function _isPaginated(array $params): bool
    {
        return isset($params['pageSize']);
    }

}
