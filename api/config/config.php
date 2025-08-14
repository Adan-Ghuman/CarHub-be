<?php

$servername = "interchange.proxy.rlwy.net";
$username = "root";
$password = "RBexaveASpsEcScgfLHIfTrkpsvrQjzO";
$database = "railway";
$port = 44546;

// Define constants for PDO usage (only if not already defined)
if (!defined('DB_HOST')) define('DB_HOST', $servername);
if (!defined('DB_USER')) define('DB_USER', $username);
if (!defined('DB_PASS')) define('DB_PASS', $password);
if (!defined('DB_NAME')) define('DB_NAME', $database);
if (!defined('DB_PORT')) define('DB_PORT', $port);

// Create MySQLi connection with port
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check MySQLi connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create PDO connection for APIs that require it
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;port=$port;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("PDO Connection failed: " . $e->getMessage());
    die("Database connection failed");
}

            
