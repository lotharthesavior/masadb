<?php

/**
 * MasaDB install program
 */

// phpinfo();exit;

session_start();

require __DIR__ . "/vendor/autoload.php";

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use \Git\Coyl\Git;
use Models\Generic;

if( isset($_POST) && !empty($_POST) ){ // post

	$post_data = $_POST;


	$config_data = json_encode($post_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	// echo "<pre>";var_dump(json_encode($config_data));exit;

	// create the config file ------------ 1
	$adapter = new Local(__DIR__);
	$filesystem = new Filesystem($adapter);

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
	\Coyl\Git\Git::create($post_data['database-address']);
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

    $git_repo = new \Coyl\Git\GitRepo($post_data['database-address']);
    $git_repo->add();
    $git_repo->commit("Initial commit.");
	// --


	// header("Location: index.php");
	exit("End of Post part.");

}else{ // form

	$base_dir = __DIR__;

	include "themes/masa1/install.php";

}