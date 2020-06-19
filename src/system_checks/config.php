<?php

return function () {
    if (!file_exists(__DIR__ . "/../../config.json")) {
        echo <<<OUTPUT
\nFile not created: config.json\n
You can copy and customize the file: config.json.sample\n
This is part of the installation step that you can find here:\n
    https://repository.wordstree.com/masa.tech/masadb/src/master/Readme.md";\n\n
OUTPUT;
        die();
    }
};
