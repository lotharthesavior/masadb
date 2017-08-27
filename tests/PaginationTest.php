<?php

require __DIR__ . "/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use GuzzleHttp\Client;

/**
 * @covers GenericModel
 */
final class PaginationTest extends TestCase
{
    /**
     * GuzzleHttp\Client instance
     */
    protected $http;

    /**
     * Array
     */
    protected $config;

    /**
     * Array
     */
    protected $authorization;
    
    /**
     *
     */
    public function setUp(){
        $this->config = [];

        $config_json = file_get_contents("config.json");
        
        $this->config['settings'] = json_decode($config_json, true);

        $this->http = new Client([
            'base_uri' => "https://" . $this->config['settings']['domain'],
            'timeout'  => 10.0,
        ]);

        $this->authorization = self::loginOAuthCredentialsAuth( $this->http );

        self::createDummyDataToTest();
    }

    /**
     * @afterClass
     */
    // public static function tearDownTestData(){
        // $generic = new \Models\Generic(
        //     // \Models\Interfaces\FileSystemInterface 
        //     new \Models\FileSystem\FileSystemBasic,
        //     // \Models\Interfaces\GitInterface
        //     new \Models\Git\GitBasic,
        //     // \Models\Interfaces\BagInterface
        //     new \Models\Bag\BagBasic
        // );

        // $generic->setDatabase("test");

        // $generic->setClientId("1");

        // $results = $generic->search("title", "Lorem Ipsum");

        // foreach ($results as $key => $record) {
        //     $generic->delete( $record->getId() );
        // }
    // }

    /**
     * This method exists as a test on OAuthTest.php
     */
    public static function loginOAuthCredentialsAuth( Client $http ){
        $response = $http->request('POST', '/access_token', [
            'headers' => [
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => '1',
                'client_secret' => 'e776dbd85f227b0f6851d10eb76cdb04903b9632',
                'scope'         => 'basic'
            ],
            'verify' => false
        ]);

        $json_result = $response->getBody()->getContents();

        $parsed_result = json_decode($json_result, true);

        return $parsed_result;
    }

    /**
     * This method exists in the GenericModelTest.php
     */
    public static function createDummyDataToTest(){
        
        $generic = new \Models\Generic(
            // \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
        );

        $generic->setClientId("1");

        $generic->setDatabase("test");

        for ($i=0; $i < 10; $i++) {
            $generic->save([
                "content" => [
                    "title" => "Lorem Ipsum " . ($i + 1),
                    "content" => "Content Content ..."
                ]
            ]);
        }

    }

    public function testPostRequestWithoutPaginationResult(){
        $response = $this->http->request('POST', '/test/search', [
            'headers' => [
                'Authorization' => $this->authorization["access_token"],
                'ClientId' => '1',
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [],
            'verify' => false
        ]);

        $json_result = $response->getBody()->getContents();

        $parsed_result = json_decode($json_result);
        // var_dump($parsed_result);exit;

        $this->AssertTrue( !isset($parsed_result->pages) );
        $this->AssertTrue( isset($parsed_result->results) );
        $this->AssertTrue( is_object($parsed_result) );
    }

    public function testPostRequestPaginationResult(){
        $page_size = 2;

        $response = $this->http->request('POST', '/test/search', [
            'headers' => [
                'Authorization' => $this->authorization["access_token"],
                'ClientId' => '1',
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'pageSize' => '2',
                'page' => '1'
            ],
            'verify' => false
        ]);

        $json_result = $response->getBody()->getContents();

        $parsed_result = json_decode($json_result);

        $this->AssertTrue( isset($parsed_result->pages) );
        $this->AssertTrue( isset($parsed_result->results) );
        $this->AssertTrue( is_object($parsed_result) );
        $this->AssertTrue( count($parsed_result->results) <= (count($parsed_result->pages) * $page_size) );
    }

    public function testPostRequestPaginationResultTimeout(){
        for ($i=0; $i < 300; $i++) { 
            self::createDummyDataToTest();
        }

        $before = new DateTime();
        $page_size = 2;

        $response = $this->http->request('POST', '/test/search', [
            'headers' => [
                'Authorization' => $this->authorization["access_token"],
                'ClientId' => '1',
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'pageSize' => '2',
                'page' => '1'
            ],
            'verify' => false
        ]);

        $after = new DateTime();

        $interval = $after->diff($before);

        $this->AssertTrue( $interval->s < 3 );
    }
}

