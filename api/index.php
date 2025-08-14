<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Basic routing
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove the script name from the request URI if the app is in a subdirectory
$path = str_replace(dirname($script_name), '', $request_uri);
$path = trim($path, '/');
$path_parts = explode('/', $path);

// Simple router
if (isset($path_parts[0])) {
    $api_name = $path_parts[0];
    $api_path = __DIR__ . '/../src/' . $api_name;

    if (is_dir($api_path)) {
        // Look for a handler file, e.g., index.php or a file with the second part of the path
        $handler_file = null;
        if (isset($path_parts[1])) {
            $potential_handler = $api_path . '/' . $path_parts[1] . '.php';
            if (file_exists($potential_handler)) {
                $handler_file = $potential_handler;
            }
        } else {
            $potential_handler = $api_path . '/index.php';
             if (file_exists($potential_handler)) {
                $handler_file = $potential_handler;
            }
        }

        if ($handler_file) {
            require $handler_file;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
