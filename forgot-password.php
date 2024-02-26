<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
require_once 'connection.php';
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
    $fullName = mysqli_real_escape_string($con, $_POST['fullName']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $phoneNumber = mysqli_real_escape_string($con, $_POST['phoneNumber']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Verify the uniqueness of email, phone number, and username
    $verify_query = mysqli_query($con, "SELECT * FROM userdata WHERE email='$email' OR phoneNumber='$phoneNumber' OR username='$username'");

    // Check if the username, email, or phone number already exists
    if (mysqli_num_rows($verify_query) > 0) {
        $error_message = "Username, Email, or Phone Number already exists";
    } else {
        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert query to add the user to the database
        // $sqlInsert = ;
        mysqli_query($con, "INSERT INTO userdata (fullname, gender, phoneNumber, email, username, password) VALUES ('$fullName', '$gender', '$phoneNumber', '$email', '$username', '$hashedPassword')");
        // Check if the query was successful
        if (mysqli_affected_rows($con) > 0) {
            $error_message = "Registration successful. You can now <a href='Login.php'>Login</a>.";
        } else {
            $error_message = "Error: " . mysqli_error($con);
        }
    }
}
?>

<div class="Menu">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <div class="form-group">
            <h1>Pilih Metode recovery</h1>
            <label for="phoneNumber">Phone Number:</label>
            <input type="tel" id="phoneNumber" name="phoneNumber" pattern="[0-9]+" required><br><br>

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
</body>
</html>