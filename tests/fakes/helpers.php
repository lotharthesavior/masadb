<?php

/**
 * Start Application Configurations
 */
function config() : array  {
	return [
        "settings" => [
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
        ]
    ];
}