<?php

/**
 * Start Application Configurations
 */
function config() : array  {
    $test_config_address = __DIR__ . "/../../config.json.test";
    
    $test_config = [];
    if (file_exists($test_config_address)) {
        $test_config = json_decode(file_get_contents($test_config_address), true);
    }

	return [
        "settings" => array_merge([
            "env" => "develop",
            "database-address" => "/home/savio/Code/Playground/masadb/data-test",
            "test-database-dir-name" => "data-test", // this is test
            "domain" => "localhost",
            "domain-fallback" => "localhost",
            "protocol" => "http",
            "displayErrorDetails" => "true",
            "case_sensitive" => false,
            "private_key" => "/var/www/html/certs/masadb.key",
            "public_key" => "/var/www/html/certs/masadb.pub",
            "timezone" => "America/Toronto",
            "no_cache" => true,
            "swoole" => true,
            "raw_files" => true
        ], $test_config),
    ];
}