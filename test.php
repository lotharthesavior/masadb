<?php

require __DIR__ . "/vendor/autoload.php";

use PHPUnit\Framework\TestCase;

use Git\Git;
use Git\Console;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

/**
 * @covers GenericModel
 */
final class GenericModelTest extends TestCase
{

    // --- GitDAO abstract methods --- //
    
    /**
     *
     */
    public function setUp(){
        $this->generic = new \Models\Generic;
        $this->generic->setDatabase("test");
    }

    /**
     * @after
     */
    public function tearDownTestData(){
        $repo = Git::open(new Console, __DIR__ . '/../data');  // -or- Git::create('/path/to/repo')

        $adapter = new Local(__DIR__.'/../data');
        $filesystem = new Filesystem($adapter);
        $filesystem->deleteDir(__DIR__.'/../data/test');

        $repo->add('.');
        $repo->commit('Cleaning test.');
    }

    /**
     * @todo test if it is a bag
     */
    public function testCreateTest(){
        $this->generic->save([
            "title" => "test data",
            "content" => "test data content"
        ]);
        $this->assertEquals(true, file_exists(__DIR__ . '/../data/test/1/data/1.json'));
        // TODO: test if it is a bag
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

