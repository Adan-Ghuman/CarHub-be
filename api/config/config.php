<?php

$servername = "interchange.proxy.rlwy.net";
$username = "root";
$password = "RBexaveASpsEcScgfLHIfTrkpsvrQjzO";
$database = "railway";
$port = 44546;

// Define constants for PDO usage
define('DB_HOST', $servername);
define('DB_USER', $username);
define('DB_PASS', $password);
define('DB_NAME', $database);
define('DB_PORT', $port);

// Create MySQLi connection with port
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check MySQLi connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} = "localhost";
$username = "root";
$password = "";
$database = "if0_39677740_carhub";

// Define constants for PDO usage
define('DB_HOST', $servername);
define('DB_USER', $username);
define('DB_PASS', $password);
define('DB_NAME', $database);

// Create MySQLi connection
$conn = new mysqli($servername, $username, $password, $database);

// Check MySQLi connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create PDO connection for APIs that require it
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("PDO Connection failed: " . $e->getMessage());
    die("Database connection failed");
}

            
