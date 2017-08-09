<?php

require __DIR__ . "/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use GuzzleHttp\Client;

/**
 * @covers GenericModel
 */
final class OauthCredentialsTest extends TestCase
{

    protected $http;
    
    /**
     *
     */
    public function setUp(){
        $config_json = file_get_contents("config.json");
        $config['settings'] = json_decode($config_json, true);
        // var_dump($config['settings']['domain']);exit;

        $this->http = new Client([
            'base_uri' => "https://" . $config['settings']['domain'],
            'timeout'  => 2.0,
        ]);
    }

    /**
     * @afterClass
     */
    public static function tearDownTestData(){
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
    }

    public function testOAuthCredentialsAuth(){
        $response = $this->http->request('POST', '/access_token', [
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

        var_dump($response->getBody()->getContents());exit;

        $this->assertEquals(false, true);
    }
}

