<?php
require_once 'connection.php';

// Extract the verification token from the URL
$verificationToken = isset($_GET['token']) ? $_GET['token'] : null;
$error_message = "";

if ($verificationToken) {
    // Validate the token and check expiration
    $result = mysqli_query($con, "SELECT * FROM userdata WHERE verification_token = '$verificationToken' AND token_expires_at > UNIX_TIMESTAMP(NOW())");

    if (mysqli_num_rows($result) > 0) {
        // Token is valid and not expired, activate the user's account
        mysqli_query($con, "UPDATE userdata SET AccountState = 'ACTIVE', verification_token = NULL WHERE verification_token = '$verificationToken'");
        
        // Display a success message
        echo "Account successfully activated. You can now log in.";
    } else {
        // Token is invalid, expired, or both
        echo "Invalid or expired verification token. Please check your email or contact support.";
    }
} else {
    // Token is not provided
    $error_message = "Verification token not provided.";
    // Redirect to the home page
    header('Location: Login.php');
    exit();
}
?>