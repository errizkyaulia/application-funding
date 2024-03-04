<?php
require_once 'connection.php';

// Extract the recovery token from the URL
$recoveryToken = isset($_GET['token']) ? $_GET['token'] : null;
$error_message = "";

if ($recoveryToken) {
    // Validate the token and check expiration
    $result = mysqli_query($con, "SELECT * FROM userdata WHERE verification_token = '$recoveryToken' AND token_expires_at > UNIX_TIMESTAMP(NOW())");
    // Check if the token is valid and not expired
    if (mysqli_num_rows($result) > 0) {
        // Token is valid and not expired, allow the user to reset the password
        // Display the password reset form
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reset Password</title>
            <link rel="stylesheet" type="text/css" href="style.css">
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Reset Password</h2>
                </div>
                <form action="reset-password.php" method="post">
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" name="newPassword" required>
                    </div>
                    <div class="input-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirmPassword" required>
                    </div>
                    <div class="input-group">
                        <button type="submit" class="btn" name="resetPassword">Reset</button>
                    </div>
                </form>
            </div>
        </body>
        </html>
        <?php
        // Handle form submission
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Sanitize and validate form data
            $newPassword = mysqli_real_escape_string($con, $_POST['newPassword']);
            $confirmPassword = mysqli_real_escape_string($con, $_POST['confirmPassword']);
            // Check if the passwords match
            if ($newPassword === $confirmPassword) {
                // Hash the password securely
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                // Update the user record with the new password
                mysqli_query($con, "UPDATE userdata SET password = '$hashedPassword', verification_token = NULL WHERE verification_token = '$recoveryToken'");
                // Check if the query was successful
                if (mysqli_affected_rows($con) > 0) {
                    // Display a success message
                    echo "Password successfully reset. You can now log in.";
                } else {
                    $error_message = "Error: " . mysqli_error($con);
                }
            } else {
                $error_message = "Passwords do not match";
            }
        }
    } else {
        // Token is invalid, expired, or both
        echo "Invalid or expired recovery token. Please check your email or contact support.";
    }
} else {
    // Token is not provided
    $error_message = "Recovery token not provided.";
    // Redirect to the home page
    header('Location: Login.php');
    exit();
}
?>