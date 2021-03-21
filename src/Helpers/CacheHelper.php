<?php

namespace Helpers;

use Ds\Deque;
use Ds\Sequence;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use Lotharthesavior\BagItPHP\BagIt;

use Models\Record;

class CacheHelper
{
    /**
     * @param string $directory
     *
     * @return Filesystem
     */
    private function getFileSystem(string $directory): Filesystem
    {
        $adapter = new Local($directory);
        $filesystem = new Filesystem($adapter);

        return $filesystem;
    }

    /**
     * @param int $client
     * @param string $database
     * @param string $database_full_address
     *
     * @return Sequence
     */
    private function getAllPhysicalRecords(
        int $client,
        string $database,
        string $database_full_address = ''
    ): Sequence
    {
        $contents = new Deque(scandir($database_full_address));
        $contents = $contents->filter(function ($dir) {
            return $dir != "."
                && $dir != ".."
                && $dir != ".git";
        });

        $contents->map(function ($path) use ($client, $database) {
            return $this->buildRecordFromPath($path, $client, $database);
        });

        return $contents;
    }

    /**
     * This method retrieve the root path of a specific client/database
     *
     * @param string $client
     * @param string $database
     *
     * @return string
     */
    private function getRootPath(int $client, string $database): string
    {
        return getcwd()
            . DIRECTORY_SEPARATOR
            . 'data'
            . DIRECTORY_SEPARATOR
            . 'client_' . $client
            . DIRECTORY_SEPARATOR
            . $database
            . DIRECTORY_SEPARATOR;
    }

    /**
     * This method build the records according to the path
     *
     * @param string $path
     * @param int $client
     * @param string $database
     *
     * @return Record
     */
    public function buildRecordFromPath(string $path, int $client, string $database): Record
    {
        $root_path = $this->getRootPath($client, $database);

        $record_instance = new Record;

        // Avoid 2 bars together.
        if (
            substr($root_path, -1) == "/"
            && $path[0] == "/"
        ) {
            $path = substr($path, 1);
        }

        // Avoid file inside an existent bag.
        $path_for_bag = $path;
        if (file_exists($root_path . $path)) {
            $path_for_bag = $root_path . $path;
        }

        $record_instance->loadRowStructureSimpleDir($root_path, $path);

        $data_path = $root_path
            . $path
            . DIRECTORY_SEPARATOR
            . "data"
            . DIRECTORY_SEPARATOR;

        if (file_exists($data_path)) {
            $bag = new BagIt($path_for_bag);
        }

        if (isset($bag) && (bool) $bag->isValid()) {

            $data_filesystem = $this->getFileSystem($data_path);
            $data_contents = $data_filesystem->listContents("", true);

            foreach ($data_contents as $key => $_file)
                $record_instance->setFileContent((array)json_decode(file_get_contents($data_path . $_file['path'])));

        } else {

            $record_instance->setFileContent((array)json_decode(file_get_contents($root_path . $path)));

        }

        return $record_instance;
    }

    /**
     * @param $data_value
     *
     * @return void
     */
    public function setData($data_value): void
    {
        $this->data = $data_value;
    }

    /**
     * @return Deque
     */
    public function getData(): Deque
    {
        if ($this->data === null) {
            $this->data = new Deque();
        }

        return $this->data;
    }

    /**
     * @param Int $client
     * @param String $database
     *
     * @return $this
     */
    public function getAllRecords($client, $database, $database_full_address = '')
    {
        $records = $this->getAllPhysicalRecords($client, $database, $database_full_address);

        $this->setData($records);

        return $this;
    }

    /**
     *
     */
    public function jsonSerialize()
    {
        return serialize($this->data);
    }

    /**
     * @param String $database - this is the database address inside
     *                           the "cache" directory, eg.: /client_1/users
     *
     * @return void
     */
    public function persistCache($database)
    {
        $filesystem = $this->getFileSystem(__DIR__ . '/../../');

        if (!$filesystem->has("cache"))
            $filesystem->createDir("cache");

        $cache_dir = "cache/" . $database;
        if (!$filesystem->has($cache_dir))
            $filesystem->createDir($cache_dir);

        $filesystem->put($cache_dir . "/all", serialize($this->data));
    }

    /**
     * This method merge the new record with the current data
     *
     * @param Record $new_record
     *
     * @return Deque|false
     */
    public function merge(Record $new_record)
    {
        $this->data->push($new_record);
        return $this->data;
    }

}
