<?php session_start(); 

require("config.php");

// Create connection
$conn = new mysqli(SERVER_HOSTNAME, USERNAME, PASSWORD, DATABASE_NAME);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}?>
