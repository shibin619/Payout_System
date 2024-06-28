<?php
$servername = "localhost";
$username = "root";
$password = "";
$database= "multi_level_affiliate_payout_system";

// Create connection
$conn = new mysqli($servername, $username, $password,$database);

session_start();

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

?>