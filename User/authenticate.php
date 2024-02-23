<?php
if (!isset($_SESSION['user'])) {
	//Set error message
	echo $_SESSION['error_message'] = "Please log in first to access this page";
	// Redirect to the login page
	header("Location: ../Login.php");
	exit();
}

// Get user ID from the username
$userid = $_SESSION['user'];

// If the user ID is not found, redirect to the login page with an error message
if (empty($userid)) {
	// Redirect to the login page with an error message
	header("Location: Login.php");
	exit();
} else {
	// Retrieve user information from the database
	//echo $userid;
}
?>