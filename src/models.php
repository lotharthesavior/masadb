<?php

use Models\Bag\BagBasic;
use Models\FileSystem\FileSystemBasic;
use Models\Generic;
use Models\Git\GitBasic;

$container['Generic'] = function ($c) {
    return new Generic(
        new FileSystemBasic, // FileSystemInterface
        new GitBasic,        // GitInterface
        new BagBasic         // BagInterface
    );
};
