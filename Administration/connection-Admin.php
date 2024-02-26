<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pembebanan";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);
// Set the timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>