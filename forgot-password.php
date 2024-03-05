<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h1 {
            font-size: 24px;
            text-align: center;
            color: #333;
        }

        .Menu {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #cccccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #555;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            width: 100%;
            text-align: center;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .Login {
            text-align: center;
            margin-top: 20px;
        }

        .login-button {
            color: #008CBA;
            background-color: transparent;
            border: green;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php
require_once 'connection.php';
require 'Administration/PHPMailer/vendor/autoload.php'; // Include Composer autoloader

// Display error message if set
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    echo "<script>alert('$message');</script>";
    unset($_SESSION['error_message']); // Clear the error message after displaying
}
$error_message = "";
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handle form submission

    // Sanitize and validate form data
    $email = mysqli_real_escape_string($con, $_POST['email']);

    // Verify the uniqueness of email, phone number, and username
    $verify_query = mysqli_query($con, "SELECT * FROM userdata WHERE email='$email'");

    // Check if the email or phone number is exists
    if (mysqli_num_rows($verify_query) > 0) {
        // Generate recovery token
        $recoveryToken = generateRecoveryToken();

        // Update the user record with the recovery token and expiration timestamp
        $expirationTimestamp = time() + 24 * 60 * 60; // 24 hours

        // Update the user record with the recovery token and expiration timestamp
        mysqli_query($con, "UPDATE userdata SET verification_token = '$recoveryToken', token_expires_at = '$expirationTimestamp' WHERE email = '$email'");

        // Check if the query was successful
        if (mysqli_affected_rows($con) > 0) {
            // Build recovery link
            $recoveryLink = "https://er-apps.alwaysdata.net/reset-password.php?token=$recoveryToken";

            // Send recovery email
            sendRecoveryEmail($email, $recoveryLink);
        } else {
            $error_message = "Error: " . mysqli_error($con);
        }
    } else {
        // Email or phone number is not exists
        $error_message = "Email or Phone Number is not exists";
    }
}
// Function to generate a random recovery token
function generateRecoveryToken() {
    return bin2hex(random_bytes(32)); // Generates a 64-character hex token
}
// Function to send a recovery email
function sendRecoveryEmail($recipient, $recoveryLink) {
    // Include your SMTP configuration
    require 'Administration/config.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    // Configure PHPMailer with your SMTP settings
    $mail->isSMTP();
    $mail->Host = SMTP_SERVER;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;

    $mail->setFrom(SMTP_USERNAME, 'Admin of er-apps');
    $mail->addAddress($recipient);
    $mail->Subject = 'Account Recovery';

    $mail->Body = "
        <p>Please click the link below to change your account password:</p>
        <p><a href='$recoveryLink'>$recoveryLink</a></p>

        <br><br>

        <strong>Note:</strong> Do not share this link with others. The link will expire after 24 hours.

        <br><br>

        If you didn't request a password reset, please contact the admin.

        <br><br>

        Regards,<br>
        Admin of er-apps
    ";

    // Set the email body as HTML
    $mail->isHTML(true);


    if ($mail->send()) {
        // Create a success message
        $error_message = "Email send successful. Check your email to reset your password.";
        $_SESSION['error_message'] = $error_message ;
        header("Location: Login.php");
        exit();
        
    } else {
        // Error handling for email sending failure
        $error_message = "Error in Mailing: " . $mail->ErrorInfo;
    }
}
?>

<div class="Menu">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <div class="form-group">
            <h1>Pilih Metode recovery</h1>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>
        </div>

        <input type="submit" name="submit" value="Send Request">
    </form>

    <?php
        // Display error message if set
        if (!empty($error_message)) {
            echo "<div class='message error'>
                    <p>$error_message</p>
                </div><br>";
        }
    ?>

    <div class="Login">
        <p>Still Remember Your Accout?</p>
        <a href="Logout.php" class="login-button">Back to Login</a>
    </div>
</div>
</body>
</html>