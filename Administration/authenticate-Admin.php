<?php
if (!isset($_SESSION['admin'])) {
	// Redirect to the login page
	echo $_SESSION['error_message'] = "Please log in first to access this page";
	header("Location: ../Login.php");
	exit();
}

// Get user ID from the username
$AdminID = $_SESSION['admin'];

//Check if the AdminLevel is allowed to access some pages
$queryAdminLevel = "SELECT AdminName, AdminLevel FROM admindata WHERE AdminID = ?";
$stmtAdminLevel = $con->prepare($queryAdminLevel);
$stmtAdminLevel->bind_param("i", $AdminID);
$stmtAdminLevel->execute();
$stmtAdminLevel->bind_result($AdminName, $AdminLevel);
$stmtAdminLevel->fetch();
$stmtAdminLevel->close();

//Store the allowed AdminLevel in an session
$_SESSION['AdminName'] = $AdminName;
$_SESSION['AdminLevel'] = $AdminLevel;

// If the user ID is not found, redirect to the login page with an error message
if (empty($AdminID)) {
	// Redirect to the login page with an error message
	header("Location: ../Login.php");
	exit();
} else {
	// Retrieve user information from the database
	//echo $AdminID;
	//echo " as ";
	//echo $AdminLevel;
}
?>