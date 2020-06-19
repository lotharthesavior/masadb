<?php

$required_settings = [
    'env' => 'Missing "env" key.',
    'database-address' => 'Missing "database-address" key.',
    'domain' => 'Missing "domain" key.',
    'protocol' => 'Missing "protocol" key.',
    'displayErrorDetails' => 'Missing "displayErrorDetails" key.',
    'case_sensitive' => 'Missing "case_sensitive" key.',
    'private_key' => "Missing \"private_key\" key. You can run this command:\nopenssl req -new -newkey rsa:2048 -nodes -keyout server.key -out server.csr",
    'public_key' => "Missing \"public_key\" key. Assuming that you ahve the private key, you can run the following command to solve this issue:\nopenssl rsa -in server.key -pubout > server.pub",
    'timezone' => 'Missing "timezone" key. e.g.: America/Toronto',
    'swoole' => 'Missing "swoole" key.',
];

return function () use ($required_settings) {
    global $config;

    $errors = [];
    $settings_keys = array_keys($config['settings']);

    foreach ($required_settings as $key => $value) {
        if (!in_array($key, $settings_keys)) {
            $errors[] = $value;
        }
    }

    if (count($errors) > 0) {
        $imploded_errors = implode("\n- ", $errors);
        echo <<<OUTPUT
\nConfiguration file (config.json) has some missing items:\n
- {$imploded_errors}\n\n
OUTPUT;
        die;
    }
};
