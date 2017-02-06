<?php

/**
 * @todo 1. create class for navigation through the repository
 * @todo 2. create navigation itself though the repository
 */

require __DIR__ . '/vendor/autoload.php';

// use \Git\Coyl\Git;

// require_once('Git.php');

$repo = \Coyl\Git\Git::open('.');  // -or- Git::create('/path/to/repo')

echo "<pre>";var_dump($repo);exit;

// code example for the usage of the Git class
// $repo->add('.');
// $repo->commit('Some commit message');
// $repo->push('origin', 'master');

// list all files in the root directory
// git ls-tree HEAD

// list all files in all directories
// git ls-tree --full-tree -r HEAD