<?php

namespace Models\Abstraction;

use Exception;

use Ds\Deque;

use Git\Git;
use Helpers\CacheHelper;
use League\Flysystem\Filesystem;

use Models\Traits\Pagination;
use Models\Interfaces\GitDAOInterface;
use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\Interfaces\BagInterface;

/**
 * Abstraction for the Model that keeps the data with Git
 */
abstract class GitDAO implements GitDAOInterface
{
    use Pagination;

    /** @var bool */
    protected $no_cache = true;

    /**
     * @param FileSystemInterface $filesystem
     * @param GitInterface $git
     * @param BagInterface $bag
     * @param array $config
     */
    public function __construct(
        FileSystemInterface $filesystem,
        GitInterface $git,
        BagInterface $bag,
        array $config = []
    )
    {
        $this->config = empty($config) ? config()['settings'] : $config;

        $this->resolveCacheCondition();

        $this->filesystem = $filesystem;
        $this->git = $git;
        $this->bag = $bag;
    }

    /**
     * Add to instance the cache related configuration.
     *
     * @return void
     */
    protected function resolveCacheCondition()
    {
        if (isset($this->config['no_cache'])) {
            $this->no_cache = $this->config['no_cache'];
        }
    }

    /**
     * Search for a Single Record by the id
     *
     * @param int|string $id
     *
     * @return array
     */
    public function find($id)
    {
        if ($this->isBag()) {
            $address = $this->config['database-address']
                . DIRECTORY_SEPARATOR
                . $this->_getDatabaseLocation()
                . DIRECTORY_SEPARATOR
                . $this->bag->locationOfBag($id, $this->isBag())
                . ".json";
        } else {
            $address = $this->config['database-address']
                . DIRECTORY_SEPARATOR
                . $this->_getDatabaseLocation()
                . DIRECTORY_SEPARATOR
                . $id;
        }

        if (!file_exists($address)) {
            throw new Exception("Inexistent Record.");
        }

        return file_get_contents($address);
    }

    /**
     * Find all the Records in the database
     *
     * @return Deque
     */
    public function findAll(): Deque
    {
        $result_complete = $this->getAllRecords();

        $result_complete = $this->_sortResult($result_complete);

        return $result_complete;
    }

    /**
     * @return Deque
     * @todo store cache in a async request
     *
     */
    private function getAllRecords(): Deque
    {
        return $this->getGitData();
    }

    /**
     * Execute the Full Search on Git and store cache
     *
     * @return Deque
     * @internal used to search when there is no cache
     *
     * @internal used to update cache
     */
    public function getGitData(): Deque
    {
        $results = $this->git->lsTreeHead(
            '.',
            $this->filesystem,
            $this->isBag(),
            $this->config['database-address'] . "/" . $this->_getDatabaseLocation()
        );

        $results->sort(function ($a, $b) {
            return $a->getId() > $b->getId();
        });

        return $results;
    }

    /**
     * Search for a single param
     *
     * @param string $param
     * @param string $value
     *
     * @return Deque
     * @internal Any param with field name 'logic', will be considered
     *           logic condition for the search
     *
     */
    public function search(string $param, string $value): Deque
    {
        /** @var Deque $result_complete */
        $result_complete = $this->getAllRecords();

        $result_complete = $result_complete->filter(function ($record) use ($param, $value) {
            if ($param !== 'id' && $this->isBag()) {
                return $record->multipleParamsMatch([$param => $value]);
            }

            if ($param !== 'id' && !$this->isBag()) {
                return $record->titleContentMatch([$param => $value]);
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
     * @param array $logic
     *
     * @return string '["results": \Ds\Vector, "pages": \Ds\Vector]'
     */
    public function searchRecord(array $params, $logic = []): string
    {
        /** @var Deque $result_complete */
        $result_complete = $this->getAllRecords();

        $search_params = $this->filterPaginationParams($params);

        $result_complete = $result_complete->filter(function ($record) use ($search_params, $logic) {
            $extension = $this->filesystem->getExtension($record->getAddress());
            if ($extension === 'json') {
                return $record->multipleParamsMatch($search_params, $logic);
            }
            return $record->titleContentMatch($search_params);
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
     * @param array $client_data ["id" => {int}, "content" => {array}]
     *
     * @return string|int
     */
    public function save(array $client_data)
    {
        /** @var string $local_address */
        $local_address = $this->_getDatabaseFullPathLocation();

        /** @var Filesystem $filesystem */
        $filesystem = $this->filesystem->getFileSystemAbstraction($local_address);

        if ($this->isBag()) {
            /* @var int $result */
            $result = $this->saveBag($filesystem, $client_data);
        } else {
            /* @var string $result */
            $result = $this->saveRawFile($filesystem, $client_data);
        }

        $this->saveVersion();

        return $result;
    }

    /**
     * @param Filesystem $filesystem
     * @param array $client_data
     *
     * @return bool|string
     */
    protected function saveRawFile(Filesystem $filesystem, array $client_data)
    {
        $item_address = null;

        $item_address = $client_data['content']['address'];
        $content = $client_data['content']['content'];

        if (null === $item_address) {
            $item_address = $this->_nextIdFilesystem() . '.json';
        }

        $this->last_inserted_id = $item_address;

        if (!isset($client_data['id']) || is_null($client_data['id'])) {
            $filesystem->write($item_address, $content, ['visibility' => 'public']);

            /** @var string */
            return $item_address;
        }

        /** @var bool */
        return $filesystem->update($item_address, $content);
    }

    /**
     * @param Filesystem $filesystem
     * @param array $client_data
     *
     * @return int $id
     */
    protected function saveBag(Filesystem $filesystem, array $client_data): int
    {
        $id = null;

        $item_address = null;

        $content = json_encode($client_data['content'], JSON_PRETTY_PRINT);

        if (!isset($client_data['id']) || is_null($client_data['id'])) {
            $id = $this->_nextIdFilesystem();

            $item_address = $id . '.json';

            $filesystem->write($item_address, $content);

            if ($this->isBag()) {
                $item_address = $this->createBagForRecord($id);
            }

            $this->last_inserted_id = $id;

            $this->saveVersion();

            $this->saveRecordVersion($item_address);

            return $id;
        }

        $id = $client_data['id'];

        $item_address = $this->bag->locationOfBag($id, $this->isBag());
        $item_address .= '.json';

        $result = $filesystem->update($item_address, $content);

        return $id;
    }

    /**
     * Update Cache
     *
     * @return void
     */
    public function updateCache(): void
    {
        $url = $this->config['protocol'] . '://' . $this->config['domain'] . "/update-cache-async";

        $body = [
            'database' => $this->_getDatabaseLocation()
        ];

        // TODO: implement this.
    }

    /**
     * @return bool
     */
    public function stageAndCommitAll(): bool
    {
        return $this->saveVersion();
    }

    /**
     * @param int|string $id
     *
     * @return bool
     *
     * @throws Exception
     * @internal simple registers can be simple json files, but
     *           any other type of file, have to be a BagIt.
     *
     */
    public function delete($id): bool
    {
        $database_url = $this->_getDatabaseFullPathLocation();

        // League\Flysystem\Filesystem
        $filesystem = $this->filesystem->getFileSystemAbstraction($database_url);

        if ($filesystem->has($id . '.json')) { // for json records

            $filesystem->delete($id . '.json');

        } elseif ($filesystem->has($id)) {

            $type = $this->filesystem->getType($database_url . '/' . $id);

            if ($type === 'file') {
                $filesystem->delete($id);
            } else {
                $filesystem->deleteDir($id);
            }

        } else {

            throw new Exception("Record not found!", 1);

        }

        $this->saveVersion();

        /** @var bool */
        return $this->saveRecordVersion($id, true);
    }

    /**
     * Verify if the current model is compatible with Bagit
     *
     * @return boolean
     * @internal this method analyze the trait. For a more
     *           reliable use a BagIt Instance.
     *
     */
    public function isBag(): bool
    {
        $is_bag = false;

        if (
            $this->checkBagitDependencies()
            && $this->checkBagitConfig()
            && !$this->isOAuthRelatedModel()
        ) {
            $is_bag = true;
        }

        return $is_bag;
    }

    /**
     * @return bool
     * @internal check get_loaded_extensions() it there is any extension
     *           that  the bagit package might eventually depend on
     *
     */
    private function checkBagitDependencies(): bool
    {
        return method_exists($this, 'createBagForRecord');
    }

    /**
     * Wether uses raw files (false) or bagit for records (false).
     *
     * @return bool
     */
    private function checkBagitConfig(): bool
    {
        return !$this->config['raw_files'] ?? false;
    }

    /**
     * Checks if the current model execution has OAuth2 related traits
     *
     * @return bool
     */
    private function isOAuthRelatedModel(): bool
    {
        $traits = get_declared_traits();

        $oauthRelatedTrait = 'League\OAuth2\Server\Entities\Traits\TokenEntityTrait';

        return in_array($oauthRelatedTrait, $traits);
    }

    /**
     * @param bool $no_cache
     *
     * @return void
     */
    public function setNoCache(bool $no_cache): void
    {
        $this->no_cache = $no_cache;
    }

    /**
     * @return bool
     */
    public function getNoCache(): bool
    {
        return $this->no_cache;
    }

    /**
     * Analyze the database to get the next id from Git
     *
     * @return int
     */
    protected function _nextId(): int
    {
        $ls_tree_result = $this->git->lsTreeHead(
            '.',
            $this->filesystem,
            $this->isBag(),
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
            return (int)$dir;
        });

        if ($records->count() === 0)
            return 1;

        $records->sort();

        return (int)$records->last() + 1;
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
     * @return string
     */
    protected function _getDatabaseFullPathLocation(): string
    {
        return $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
    }

    /**
     * Sort a Collection
     *
     * @param Deque $collection
     *
     * @return Deque
     */
    private function _sortResult(Deque $collection): Deque
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

}
