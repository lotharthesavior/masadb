<?php

$raw_files = true;
require __DIR__ . "/autoload.php";

use PHPUnit\Framework\TestCase;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Models\FileSystem\FileSystemBasic;
use Models\Git\GitBasic;
use Models\Bag\BagBasic;
use Models\Generic;
use Models\Exceptions\NotExistentDatabaseException;

/**
 * @covers GenericModel
 */
final class RawGenericModelTest extends TestCase
{
    /** @var Generic */
    protected $generic;

    /** @var Filesystem */
    protected $filesystem;

    /** @var array */
    protected $config;

    /** @var string */
    protected $client_id = '1';

    /** @var string */
    protected $client_dir = 'client_1';

    /**
     *
     */
    protected function setUp(): void
    {
        $this->checkPermissions();
        $this->config = config();
        $this->config['settings']['raw_files'] = true;
        $this->setFilesystem();
        $this->clearDatabase();
        $this->createDatabase();
        $this->getGeneric();
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        $this->clearDatabase();
    }

    /**
     * @todo this is about to be decided still, the goal is to
     *       determine the best permissions for this directory.
     */
    private function checkPermissions()
    {
        // dd(substr(sprintf('%o', fileperms('/tmp')), -4));
    }

    private function setFilesystem()
    {
        $path_to_db = strstr(
            $this->config['settings']['database-address'],
            $this->config['settings']['test-database-dir-name'],
            true
        );

        $adapter = new Local($path_to_db);
        $this->filesystem = new Filesystem($adapter);
    }

    private function clearDatabase()
    {
        if ($this->filesystem->has($this->config['settings']['test-database-dir-name'], false)) {
            $this->filesystem->deleteDir($this->config['settings']['test-database-dir-name']);
        }
    }

    private function createDatabase()
    {
        $database_dir = $this->config['settings']['test-database-dir-name'];
        $client_dir = $database_dir . '/' . $this->client_dir;

        if (!$this->filesystem->has($database_dir, false)) {
            $this->filesystem->createDir($database_dir);
        }

        if (!$this->filesystem->has($client_dir, false)) {
            $this->filesystem->createDir($client_dir);
        }
    }

    private function getGeneric()
    {
        if ($this->generic !== null) {
            return;
        }

        $database = "test";

        $this->generic = new Generic(
            new FileSystemBasic,
            new GitBasic,
            new BagBasic
        );

        $this->generic->setClientId($this->client_id);

        try {
            $this->generic->setDatabase($database);
        } catch (NotExistentDatabaseException $e) {
            $this->generic->createDatabase($database);
            $this->generic->setDatabase($database);
        }
    }

    /**
     * @param string $filename
     */
    private function createDummyRecord(string $filename = 'test.md')
    {
        return $this->generic->save([
            "content" => [
                "address" => $filename,
                "content" => "Content Content ...",
            ],
        ]);
    }

    /**
     *
     */
    private function getPhysicalNumberOrRecords()
    {
        $adapter = new Local("/");

        $filesystem = new Filesystem($adapter);

        $list = $this->filesystem->listContents($this->config['settings']['test-database-dir-name']);

        $list = array_filter($list, function ($item) {
            return $item['basename'] !== '.git';
        });

        return $list;
    }

    public function testSetDatabase()
    {
        $this->assertEquals("test", $this->generic->getDatabase());
    }

    public function testSetClientId()
    {
        $this->assertEquals(1, $this->generic->getClientId());
    }

    public function testFind()
    {
        $id = $this->createDummyRecord();

        $result = $this->generic->find($id);

        $this->assertEquals($result, "Content Content ...");

        try {
            $results = $this->generic->find(242343232);
        } catch (\Exception $e) {
            $results = $e->getMessage();
        }

        $this->assertEquals($results, "Inexistent Record.");
    }

    /**
     * This test is useful to test if:
     *     1. the returning class is a Deque Data Structure
     *     2. the number of physical files is the same returned
     *        but the search
     */
    public function testFindAll()
    {
        $this->createDummyRecord();

        $results = $this->generic->findAll();

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(get_class($results), 'Ds\Deque');
        $this->assertEquals(count($list), $results->count());
    }

    /**
     *
     */
    public function testSearch()
    {
        $this->createDummyRecord();

        $results = $this->generic->search("address", "test.md");

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), $results->count());
    }

    public function testSearchRecord()
    {
        $this->createDummyRecord();

        $results = $this->generic->searchRecord([
            "address" => "test.md",
        ]);
        $results = json_decode($results);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), count($results->results));
    }

    public function testSearchRecordSearchParam()
    {
        $this->createDummyRecord();

        $results = $this->generic->searchRecord([
            "content" => "Content Content ..."
        ], 1);
        $results = json_decode($results);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), count($results->results));
    }

    public function testSave()
    {
        $this->createDummyRecord();


        $results = $this->generic->searchRecord([
            "content" => "Content Content ...",
        ]);
        $results = json_decode($results);
        $results_count_before = count($results->results);

        $this->createDummyRecord('test2.md');

        $results = $this->generic->searchRecord([
            "content" => "Content Content ...",
        ]);
        $results = json_decode($results);
        $results_count_after = count($results->results);

        $this->assertTrue($results_count_after > $results_count_before);
    }

    public function testDelete()
    {
        $test_id = $this->createDummyRecord();

        $this->generic->delete($test_id);

        try {
            $results = $this->generic->find($test_id);
        } catch (Exception $e) {
            $results = $e->getMessage();
        }

        $this->assertEquals($results, "Inexistent Record.");

    }
}

