<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .btn {
            background-color: #4CAF50;
            color: #fff;
            width: 100%;
            text-align: center;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
    <?php
    require_once 'connection.php';
    $error_message = "";

    // Extract the recovery token from the URL
    $recoveryToken = isset($_GET['token']) ? $_GET['token'] : null;

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Sanitize and validate form data
        $newPassword = mysqli_real_escape_string($con, $_POST['newPassword']);
        $confirmPassword = mysqli_real_escape_string($con, $_POST['confirmPassword']);

        // Check if the passwords match
        if ($newPassword === $confirmPassword) {
            // Hash the password securely
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the user record with the new password and remove the recovery token
            mysqli_query($con, "UPDATE userdata SET password = '$hashedPassword', verification_token = NULL WHERE verification_token = '$recoveryToken'");

            // Check if the query was successful
            if (mysqli_affected_rows($con) > 0) {
                // Display a success message on session
                $error_message = "Password successfully reset. You can now log in.";
                $_SESSION['error_message'] = $error_message;
                // Redirect to the login page
                header('Location: Login.php');
                exit();
            } else {
                $error_message = "Error: Unable to reset the password.";
            }
        } else {
            $error_message = "Passwords do not match";
        }
    }
    ?>
</head>
<body>
    <?php
    // Check if the recovery token is provided
    if ($recoveryToken) {
        // Validate the token and check expiration
        $result = mysqli_query($con, "SELECT * FROM userdata WHERE verification_token = '$recoveryToken' AND token_expires_at > UNIX_TIMESTAMP(NOW())");
    
        // Check if the token is valid and not expired
        if (mysqli_num_rows($result) > 0) {
            // Token is valid and not expired, allow the user to reset the password
                // Display the password reset form
                ?>
                <div class="container">
                    <div class="header">
                        <h2>Reset Password</h2>
                    </div>
                    <form action="reset-password.php?token=<?php echo $recoveryToken; ?>" method="post">
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
                <?php
                // Display the error message if any
                if ($error_message) {
                    echo "<script>alert('$error_message');</script>";
                }
        } else {
            // Token is invalid, expired, or both
            echo "Invalid or expired recovery token. Please check your email or contact support.";
        }
    } else {
        // Token is not provided
        $error_message = "Recovery token not provided.";
        // Redirect to the home page and set the error message to session
        $_SESSION['error_message'] = $error_message;
        header('Location: Login.php');
        exit();
    }
    ?>
</body>
</html>