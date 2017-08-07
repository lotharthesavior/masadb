<?php

require __DIR__ . "/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

/**
 * @covers GenericModel
 */
final class GenericModelTest extends TestCase
{

    protected $generic;
    
    /**
     *
     */
    public function setUp(){
        $this->generic = new \Models\Generic(
            // \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
        );

        $this->generic->setDatabase("test");

        $this->generic->setClientId("1");
    }

    /**
     * 
     */
    private function createDummyRecord(){
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

        return $filesystem->listContents(__DIR__ . "/../data/client_1/test");
    }

    /**
     * @afterClass
     */
    public static function tearDownTestData(){
        $generic = new \Models\Generic(
            // \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
        );

        $generic->setDatabase("test");

        $generic->setClientId("1");

        $results = $generic->search("title", "Lorem Ipsum");

        foreach ($results as $key => $record) {
            $generic->delete( $record->getId() );
        }
    }

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

    public function testFindAll(){
        $results = $this->generic->findAll();

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), $results->count());
    }

    public function testSearch(){
        $results = $this->generic->search("title", "Lorem Ipsum");

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), $results->count());
    }

    public function testSearchRecord(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum"
        ]);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), $results->count());
    }

    public function testSearchRecordSearchParam(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum",
            "content" => "Lorem Ipsum"
        ], 1);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertEquals(count($list), $results->count());
    }

    public function testSearchRecordSearchParamAND(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum",
            "content" => "Lorem Ipsum"
        ]);

        $list = $this->getPhysicalNumberOrRecords();

        $this->assertTrue(count($list) != $results->count());
    }

    public function testSave(){
        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum"
        ]);
        $results_count_before = count($results);

        $this->createDummyRecord();

        $results = $this->generic->searchRecord([
            "title" => "Lorem Ipsum"
        ]);
        $results_count_after = count($results);

        $this->assertTrue($results_count_after > $results_count_before);
    }

    public function testDelete(){
        $test_id = $this->createDummyRecord();
        
        $this->assertTrue( (int)$test_id > 0 );

        $this->generic->delete($test_id);

        try {
            $results = $this->generic->find($test_id);
        } catch (Exception $e) {
            $results = $e->getMessage();
        }

        $this->assertEquals($results, "Inexistent Record.");

    }
}

