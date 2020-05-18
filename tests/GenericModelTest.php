<?php

require __DIR__ . "/../vendor/autoload.php";

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
final class GenericModelTest extends TestCase
{

    protected $generic;
    
    /**
     *
     */
    public function setUp()
    {
        $this->getGeneric();
    }

    private function getGeneric()
    {
        if ($this->generic !== null) return;

        $database = "test";
        $clientId = "1";

        $this->generic = new Generic(
            // \Models\Interfaces\FileSystemInterface 
            new FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new GitBasic,
            // \Models\Interfaces\BagInterface
            new BagBasic
        );

        $this->generic->setClientId($clientId);

        try {     
            $this->generic->setDatabase($database);
        } catch (NotExistentDatabaseException $e) {
            $this->generic->createDatabase($database);
            $this->generic->setDatabase($database);
        }
    }

    /**
     * @before
     */
    public function prepareData()
    {
        $this->clearDatabase();
        $this->createDummyRecord();
    }

    public function clearDatabase()
    {
        $this->getGeneric();

        $results = $this->generic->findAll();

        $results->map(function($item){
            $this->generic->delete((int) $item->getId());
        });
    }

    /**
     * 
     */
    private function createDummyRecord()
    {
        return $this->generic->save([
            "content" => [
                "title" => "Lorem Ipsum",
                "content" => "Content Content ..."
            ]
        ]);
    }
    
    /**
     * 
     */
    private function getPhysicalNumberOrRecords(){
        $adapter = new Local("/");

        $filesystem = new Filesystem($adapter);

        $list = $filesystem->listContents(__DIR__ . "/../data/client_1/test");

        $list = array_filter($list, function($item){
            return $item['basename'] !== '.git';
        });

        return $list;
    }

    /**
     * @afterClass
     */
    // public static function tearDownTestData(){
    //     $generic = new \Models\Generic(
    //         // \Models\Interfaces\FileSystemInterface 
    //         new \Models\FileSystem\FileSystemBasic,
    //         // \Models\Interfaces\GitInterface
    //         new \Models\Git\GitBasic,
    //         // \Models\Interfaces\BagInterface
    //         new \Models\Bag\BagBasic
    //     );

    //     $generic->setClientId("1");

    //     $generic->setDatabase("test");

    //     $results = $generic->search("title", "Lorem Ipsum");

    //     foreach ($results as $key => $record) {
    //         $generic->delete( $record->getId() );
    //     }
    // }

    public function testSetDatabase(){
        $this->assertEquals("test", $this->generic->getDatabase());
    }

    public function testSetClientId(){
        $this->assertEquals(1, $this->generic->getClientId());
    }

    public function testFind(){
        $id = $this->createDummyRecord();

        $result = $this->generic->find($id);
        
        $this->assertEquals(
            json_decode($result, true), 
            [
                "title" => "Lorem Ipsum",
                "content" => "Content Content ..."
            ]
        );

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
    public function testFindAll(){
        $results = $this->generic->findAll();

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(get_class($results), 'Ds\Deque');
        $this->assertEquals(count($list), $results->count());
    }

    /**
     * 
     */
    public function testSearch(){
        $results = $this->generic->search("title", "Lorem Ipsum");
        
        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), $results->count());
    }

    public function testSearchRecord(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum"
        ]);
        $results = json_decode($results);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), count($results->results));
    }

    public function testSearchRecordSearchParam(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum",
            "content" => "Lorem Ipsum"
        ], 1);
        $results = json_decode($results);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), count($results->results));
    }

    public function testSearchRecordSearchParamAND(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum",
            "content" => "Lorem Ipsum"
        ]);
        $results = json_decode($results);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertTrue(count($list) != count($results->results));
    }

    public function testSave(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum"
        ]);
        $results = json_decode($results);
        $results_count_before = count($results->results);

        $this->createDummyRecord();

        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum"
        ]);
        $results = json_decode($results);
        $results_count_after = count($results->results);

        $this->assertTrue($results_count_after > $results_count_before);
    }

    public function testDelete(){
        $test_id = $this->createDummyRecord();
        // var_dump((int)$test_id);exit;
        
        $this->assertTrue( (int)$test_id > 0 );

        $this->generic->delete($test_id);

        try {
            $results = $this->generic->find($test_id);
        } catch (Exception $e) {
            $results = $e->getMessage();
        }
        // var_dump($results);exit;

        $this->assertEquals($results, "Inexistent Record.");

    }
}

