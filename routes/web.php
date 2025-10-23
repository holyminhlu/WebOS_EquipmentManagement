<?php

// Basic route definitions for the skeleton

if (php_sapi_name() !== 'cli') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($uri === '/' || $uri === '/index.php') {
        echo file_get_contents(__DIR__ . '/../resources/views/welcome.html');
        exit;
    }

    http_response_code(404);
    echo 'Not Found';
}
