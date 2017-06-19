<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers GenericModel
 */
final class GenericModelTest extends TestCase
{

    // --- GitModel abstract methods --- //
    
    public function setUp(){
        $this->generic = new \Models\Generic;
        $this->generic->setDatabase("test");
    }

    //
    public function testCreateTest(){
        $this->generic->save([
            "title" => "test data",
            "content" => "test data content"
        ]);
        $this->assertEquals(true, file_exists(__DIR__ . '/../data/test/1'));
    }

    // public function testFind
    public function testFind(){
        $results = $this->generic->find(1);

        var_dump($results);exit;

    }

    // public function testFindAll
    // public function testSearch
    // public function testSave
    // public function testDelete
    // public function testLsTreeHead
    // public function testLoadObject
    // public function testIsBag
    // public function testLocationOfBag
    // public function testGetFileContent
    // public function testSortResult
    // public function testSortAscendingOrder
    // public function testSortDescendingOrder
}

