<?php

/**
 * MasaDB install program
 * 
 * @todo build a reset function
 */

die('deprecated!');

session_start();

require __DIR__ . "/vendor/autoload.php";

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Coyl\Git\Git;
use Models\Generic;
use \Coyl\Git\GitRepo;

$adapter = new Local(__DIR__);
$filesystem = new Filesystem($adapter);

// check if there is any configuration
if( $filesystem->has('config.lock') ){
    exit("config.lock already exists!");
}

if( isset($_POST) && !empty($_POST) ){ // post

    $post_data = $_POST;


    $config_data = json_encode($post_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    // echo "<pre>";var_dump(json_encode($config_data));exit;

    // create the config file ------------ 1
    if( $filesystem->has('config.json') ){
        $filesystem->delete('config.json');
    }
    if( !$filesystem->write('config.json', $config_data) ){
        // exit("Error: Problem writing config file!");
        echo "Error: Problem writing config file!";
    }
    // --


    // create database data address ------------ 2
    $adapter_data = new Local("/");
    $filesystem_data = new Filesystem($adapter_data);

    if( $filesystem_data->has($post_data['database-address']) ){
        // exit("Error: Directory for data already exists!");
        echo "Error: Directory for data already exists!";
    }else if( !$filesystem_data->createDir($post_data['database-address']) ){
        exit("Error: Problem creating 'database-address'!");
    }
    // --


    // turn the data directory into git repository ------------ 3
    Git::create($post_data['database-address']);
    // --


    // add oauth databases to data directoey ------------ 4
    $data = [
        "name" => "default_client",
        "user_id" => 1,
        "redirectUri" => $post_data['domain'] . "/server_response",
        "secret_key" => "e776dbd85f227b0f6851d10eb76cdb04903b9632"
    ];

    if( !$filesystem_data->createDir($post_data['database-address'] . "/oauth/clients") ){
        exit("Error: Problem creating '/oauth/clients'!");
    }
    $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if( !$filesystem_data->write($post_data['database-address'] . "/oauth/clients/1.json", $data) ){
        exit("Error: Problem writing /oauth/clients/1.json file!");
    }

    if( !$filesystem_data->createDir($post_data['database-address'] . "/oauth/access_token") ){
        exit("Error: Problem creating '/oauth/access_token'!");
    }

    try {
        // prepare main repo
        $git_repo = new GitRepo($post_data['database-address']);
        if (empty($git_repo->run('config --global --get user.name'))) {
            $git_repo->run('config --global user.name Savio');
        }
        if (empty($git_repo->run('config --global --get user.email'))) {
            $git_repo->run('config --global user.email savio@wordstree.com');
        }
        $git_repo->add();
        $git_repo->commit("Initial commit.");

        // convert oauth/client into git repo
        Git::create($post_data['database-address'] . "/oauth/clients");
        $git_repo_clients = new GitRepo($post_data['database-address'] . "/oauth/clients");
        $git_repo_clients->add();
        $git_repo_clients->commit("Initial commit.");

        // convert oauth/access_token into git repo
        Git::create($post_data['database-address'] . "/oauth/access_token");
        $git_repo_access_token = new GitRepo($post_data['database-address'] . "/oauth/access_token");
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Some debug will be needed.";
        exit;
    }
    // --

    // create the config lock file ------------ 1
    if( $filesystem->has('config.lock') ){
        $filesystem->delete('config.lock');
    }
    if( !$filesystem->write('config.lock', [
        "installed_in" => date("U")
    ]) ){
        exit("Error: Problem writing config lock file!");
    }
    // --

    // header("Location: index.php");
    exit("MasaDB is successfully installed! <a href='/'>Go home</a>");

}else{ // form

    $base_dir = __DIR__;

    include "themes/masa1/install.php";

}