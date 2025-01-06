<?php session_start(); 

require("config.php");

if (file_exists(__DIR__ ."/.htaccess") and $_SERVER['REQUEST_METHOD'] === 'GET') {
    $request = $_SERVER['REQUEST_URI'];

    if (str_ends_with($_SERVER["REQUEST_URI"], "index.php")) {
        $newUrl = dirname($request);
        header("Location: $newUrl", true, 301);
	exit;
    } elseif (strpos($request, '.php') !== false) {
        $newUrl = str_replace('.php', '', $request);
        header("Location: $newUrl", true, 301);
	exit;
    }
}

// Create connection
$conn = new mysqli(SERVER_HOSTNAME, USERNAME, PASSWORD, DATABASE_NAME);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}?>
