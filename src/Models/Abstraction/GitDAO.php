<?php

namespace Models\Abstraction;

use \Git\Git;
use \Models\Traits\Pagination;
use \Helpers\CacheHelper;

use \Ds\Deque;

/**
 *
 * Abstraction for the Model that keeps the data with Git
 *
 * @author Savio Resende <savio@savioresende.com.br>
 *
 */
abstract class GitDAO implements \Models\Interfaces\GitDAOInterface
{
    use Pagination;

    // Core instance for FileSystem interaction
    // proteced filesystem;

    // Core instance for Git interaction
    // proteced git;

    // Core instance for Bag interaction
    // proteced bag;

    // keep the config.json content parsed
    // protected $config;

    // attribute to specify the sorting type: ASC | DESC
    // protected $sortType;

    /**
     * @param \Models\Interfaces\FileSystemInterface $filesystem
     * @param \Models\Interfaces\GitInterface $git
     * @param \Models\Interfaces\BagInterface $bag
     */
    public function __construct(
        \Models\Interfaces\FileSystemInterface $filesystem,
        \Models\Interfaces\GitInterface $git,
        \Models\Interfaces\BagInterface $bag
    )
    {
        $this->config = config()['settings'];

        $this->filesystem = $filesystem;
        $this->git = $git;
        $this->bag = $bag;
    }

    /**
     * Search for a Single Record by the id
     *
     * @param int $id
     * @return array
     */
    public function find(int $id)
    {
        $address = $this->config['database-address'] . "/" . $this->_getDatabaseLocation() . "/" . $this->bag->locationOfBag($id, $this->isBag()) . ".json";
        
        if (!file_exists($address)) {
            throw new \Exception("Inexistent Record.");
        }

        $result = file_get_contents($address);

        return $result;
    }

    /**
     * Find all the Records in the database
     *
     * @return \Ds\Deque
     */
    public function findAll()
    {
        $result_complete = $this->getAllRecords();

        $result_complete = $this->_sortResult($result_complete);

        return $result_complete;
    }

    /**
     * @todo store cache in a async request
     *
     * @return mix
     */
    private function getAllRecords()
    {
        $cache_helper = new CacheHelper;
        
        // $cache_result = $cache_helper->getCacheData($this->getClientId(), $this->database);
        // if ($cache_result !== false) {
        //     return $cache_result;
        // }

        return $this->getGitData($cache_helper);
    }

    /**
     * Execute the Full Search on Git and store cache
     *
     * @internal used to update cache
     * @internal used to search when there is no cache
     * @param CacheHelper $cache_helper
     */
    public function getGitData(CacheHelper $cache_helper)
    {
        // $date1 = new \DateTime();

        $results = $this->git->lsTreeHead(
            '.',
            $this->filesystem,
            $this->isBag(),
            $this->config['database-address'] . "/" . $this->_getDatabaseLocation()
        );

        // $date2 = new \DateTime();
        // var_dump($date2->diff($date1));
        // var_dump($results);exit;

        $results->sort(function ($a, $b) {
            return $a->getId() > $b->getId();
        });

        // $cache_helper->setData($results);
        // $cache_helper->persistCache($this->_getDatabaseLocation());

        return $results;
    }

    /**
     * Execute the Full Search on Filesystem and store cache.
     *
     * This method exists as an alternative, but it didn't beat
     * the speed of the git ls-tree ($this->getGitData) speed to
     * retrieve big amount of data in the working directory.
     *
     * @internal used to update cache
     * @internal used to search when there is no cache
     * @ $cache_helper
     */
    public function getFilesystemData(CacheHelper $cache_helper)
    {
        // $date1 = new \DateTime();

        $results = $this->filesystem->listWorkingDirectory(
            $this->config['database-address'] . "/" . $this->_getDatabaseLocation(),
            $this->isBag()
        );

        // $date2 = new \DateTime();
        // var_dump($date2->diff($date1));
        // var_dump($results);exit;

        $results->sort(function ($a, $b) {
            return $a->getId() > $b->getId();
        });

        $cache_helper->setData($results);
        $cache_helper->persistCache($this->_getDatabaseLocation());

        return $results;
    }

    /**
     * Search for a single param
     *
     * @internal Any param with field name 'logic', will be considered
     *           logic condition for the search
     * @param string $param || array $param
     * @param string $value || array $value
     */
    public function search($param, $value)
    {
        $result_complete = $this->getAllRecords();

        $result_complete = $result_complete->filter(function ($record) use ($param, $value) {
            if ($param !== 'id') {
                return $record->multipleParamsMatch([$param => $value]);
            }

            if ($param === 'id' && $record->valueEqual($param, $value)) {
                return false;
            }
        });

        return $result_complete;
    }

    /**
     * Search that works with multiple params
     *
     * @param array $params
     * @return JSON | ["results": \Ds\Vector, "pages": \Ds\Vector]
     */
    public function searchRecord(array $params, $logic = [])
    {
        $result_complete = $this->getAllRecords();

        $search_params = $this->filterPaginationParams($params);

        $result_complete = $result_complete->filter(function ($record) use ($search_params, $logic) {
            return $record->multipleParamsMatch($search_params, $logic);
        });

        $result_complete->sort(function ($a, $b) {
            return (int)$a->getId() > (int)$b->getId();
        });

        if (!$this->_isPaginated($params)) {
            return json_encode(["results" => $result_complete]);
        }

        $result_page = $this->_getPage($result_complete, $params);

        return json_encode($result_page);
    }

    /**
     * Persist record
     *
     * @param array $client_data | eg.: ["id" => {int}, "content" => {array}]
     */
    public function save(array $client_data)
    {
        $client_data = (object) $client_data;

        $local_address = $this->_getDatabaseFullPathLocation();

        // @var League\Flysystem\Filesystem
        $filesystem = $this->filesystem->getFileSystemAbstraction($local_address);

        $content = json_encode($client_data->content, JSON_PRETTY_PRINT);

        $id = null;

        $item_address = null;

        if (
            !isset($client_data->id)
            || is_null($client_data->id)
        ) {
            $id = $this->_nextIdFilesystem();

            $item_address = $id . '.json';

            $filesystem->write($item_address, $content);

            if ($this->isBag()) {
                $item_address = $this->createBagForRecord($id);
            }

            $this->last_inserted_id = $id;

            $this->saveVersion();

            $result = $this->saveRecordVersion($item_address);

            return $id;
        }

        $id = $client_data->id;

        $item_address = $this->bag->locationOfBag($id, $this->isBag());
        $item_address .= '.json';

        $result = $filesystem->update($this->bag->locationOfBag($id, $this->isBag()) . ".json", $content);

        $this->saveVersion();

        $result = $this->saveRecordVersion($id);

        return $id;

    }

    /**
     * Update Cache
     */
    public function updateCache()
    {
        $url = $this->config['protocol'] . '://' . $this->config['domain'] . "/update-cache-async";

        $body = [
            'database' => $this->_getDatabaseLocation()
        ];

        // This is commented because it doesn't perform in the necessary
        // speed. The alternative is to update the cache "manually".
        // $header = [
        //     'ClientId' => $_SERVER['HTTP_CLIENTID'],
        //     'Authorization' => $_SERVER['HTTP_AUTHORIZATION'],
        //     'Content-Type' => $_SERVER['HTTP_CONTENT_TYPE']
        // ];
        // \Helpers\AppHelper::curlPostAsync($url, $body, $header);
        // \Helpers\AppHelper::curlPostAsync($url, $body);
    }

    /**
     * This method adds a new filesystem record to the cache
     *
     * @param int $item
     * @return void
     */
    public function addItemToCache(int $item)
    {
        $cache_helper = new CacheHelper;

        $cache_helper->getCacheData(
            $this->client_id,
            $this->database,
            'all',
            true
        );

        $item_path = (string) $item;
        $new_record = $cache_helper->buildRecordFromPath($item_path, $this->client_id, $this->database);
        $cache_helper->merge($new_record);
        $cache_helper->persistCache($this->_getDatabaseLocation());
    }

    /**
     * This method adds a new filesystem record to the cache
     *
     * @param int $item
     *
     * @return void
     */
    public function removeItemFromCache(int $item)
    {
        $cache_helper = new CacheHelper;

        $cache_helper->getCacheData(
            $this->client_id,
            $this->database,
            'all',
            true
        );

        $cache_data = $cache_helper->getData();
        $cache_data = $cache_data->filter(function ($record) use ($item) {
            return (int) $record->getId() !== (int) $item;
        });
        $cache_helper->setData($cache_data);

        $cache_helper->persistCache($this->_getDatabaseLocation());
    }

    /**
     *
     */
    public function stageAndCommitAll()
    {
        return $this->saveVersion();
    }

    /**
     *
     * @internal simple registers can be simple json files, but
     *           any other type of file, have to be a BagIt.
     * @param int $id
     */
    public function delete(int $id)
    {

        $database_url = $this->_getDatabaseFullPathLocation();

        // League\Flysystem\Filesystem
        $filesystem = $this->filesystem->getFileSystemAbstraction($database_url);

        if ($filesystem->has($id . '.json')) {

            $filesystem->delete($id . '.json');

        } elseif ($filesystem->has($id)) {

            $filesystem->deleteDir($id);

        } else {

            throw new \Exception("Record not found!", 1);

        }

        $this->saveVersion();

        $result = $this->saveRecordVersion($id, true);

        return $result;

    }

    /**
     * Verify if the current model is compatible with Bagit
     *
     * @internal this method analyze the trait. For a more
     *           reliable use a BagIt Instance.
     *
     * @return boolean
     */
    public function isBag()
    {

        $is_bag = false;

        if (method_exists($this, 'createBagForRecord')) {

            $is_bag = true;

        }

        return $is_bag;

    }

    /**
     * Analyze the database to get the next id from Git
     *
     * @return int
     */
    protected function _nextId()
    {
        // var_dump($this->git);exit;
        $ls_tree_result = $this->git->lsTreeHead(
        // $this->_getDatabaseLocation() . '/',
            '.',
            $this->filesystem,
            $this->isBag(),
            // $this->config['database-address'] . '/' . $this->database
            $this->config['database-address'] . "/" . $this->_getDatabaseLocation()
        );

        if ($ls_tree_result->count() < 1)
            return 1;

        $ls_tree_result = $ls_tree_result->map(function ($record) {
            return (int)$record->getId();
        });

        $ls_tree_result->sort();

        return $ls_tree_result->last() + 1;
    }

    /**
     * Analyze the database to get the next id from Filesystem
     *
     * @return int
     */
    protected function _nextIdFilesystem()
    {
        $database = $this->config['database-address'] . "/" . $this->_getDatabaseLocation();

        $records = new Deque(scandir($database));

        $records = $records->filter(function ($dir) {
            return $dir != "."
                && $dir != ".."
                && $dir != ".git";
        });

        $records = $records->map(function ($dir) {
            return (int) $dir;
        });

        if ($records->count() === 0)
            return 1;

        $records->sort();

        return (int) $records->last() + 1;
    }

    /**
     * Analyze the presence of client_id and add it to the database
     * folder to keep data into the client scope
     *
     * @return string $database_location
     */
    protected function _getDatabaseLocation(): string
    {
        $database_location = "";

        if (isset($this->client_id) && !empty($this->client_id)) {
            $database_location .= "client_" . $this->client_id[0] . '/';
        }

        $database_location .= $this->database;

        return $database_location;
    }

    /**
     *
     * @return string
     */
    protected function _getDatabaseFullPathLocation(): string
    {
        return $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
    }

    /**
     * Sort a Collection
     *
     * @todo this function will encapsulate the sorting functions
     * @todo validate $this->sortType
     *
     * @param array $collection
     */
    private function _sortResult($collection)
    {

        $sort_type = "ASC";
        if (
            isset($this->sortType)
            && !empty($this->sortType)
        ) {
            $sort_type = $this->sortType;
        }


        switch ($sort_type) {

            case 'ASC':
                $collection->sort(function ($a, $b) {
                    return (int)$a->getId() > (int)$b->getId();
                });
                break;

            case 'creation_DESC':
                $collection->sort(function ($a, $b) {
                    return (int)$a->getId() < (int)$b->getId();
                });
                break;

        }

        return $collection;

    }

    /**
     * Sort Ascending
     *
     * @param array $collection
     */
    private function _sortAscendingOrder($collection)
    {

        usort($collection, function ($a, $b) {
            return (int)$a->id > (int)$b->id;
        });

        return $collection;

    }

    /**
     * Sort Ascending
     *
     * @param array $collection
     */
    private function _sortCreationDescendingOrder($collection)
    {

        usort($collection, function ($a, $b) {
            return (int)$b->getFileTimestamp() > (int)$a->getFileTimestamp();
        });

        return $collection;

    }

}
