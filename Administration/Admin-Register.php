<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN REGISTRATION</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
require_once "../connection.php";
require_once "authenticate-Admin.php";

// Check if the Admin is authorized to access this page
if (!isset($_SESSION['AdminLevel']) || ($_SESSION['AdminLevel'] !== 'KING')) {
    echo '<script>alert("You are not authorized to access this page");</script>';
    echo '<script>window.location.href = "Admin-Page.php";</script>';
    exit(); // Add this line to stop further execution if unauthorized
}

$error_message = "";
$error_search = "";

// Function to retrieve admin data by username from the database
function getAdminByUsername($AdminUserName, $con) {
    $queryAdmin = "SELECT * FROM admindata WHERE AdminUserName = ?";
    $stmt = mysqli_prepare($con, $queryAdmin);
    mysqli_stmt_bind_param($stmt, "s", $AdminUserName);
    mysqli_stmt_execute($stmt);
    $resultAdmin = mysqli_stmt_get_result($stmt);
    $adminData = mysqli_fetch_assoc($resultAdmin);
    mysqli_stmt_close($stmt);

    // Example data for demonstration purposes
    return [
        "AdminID" => $adminData['AdminID'] ?? "",
        "AdminName" => $adminData['AdminName'] ?? "",
        "AdminUserName" => $adminData['AdminUserName'] ?? $AdminUserName,
        "AdminPassword" => "",
        "AdminLevel" => $adminData['AdminLevel'] ?? "",
        "AdminStatus" => $adminData['AdminStatus'] ?? "",
    ];
}

// Handle search logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {
    $searchUsername = $_POST["AdminUserName"];
    $adminData = getAdminByUsername($searchUsername, $con);

    // Populate the form fields with retrieved admin data
    foreach ($adminData as $key => $value) {
        $_POST[$key] = $value;
    }

    // Handle non-existent records
    if (!$adminData['AdminID']) {
        // Admin not found, handle accordingly (e.g., set default values or display an error)
        $error_search = "Admin not found.";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    
    // Sanitize and validate form data
    $AdminID = mysqli_real_escape_string($con, $_POST['AdminID']);
    $AdminName = mysqli_real_escape_string($con, $_POST['AdminName']);
    $AdminUserName = mysqli_real_escape_string($con, $_POST['AdminUserName']);
    $AdminPassword = mysqli_real_escape_string($con, $_POST['AdminPassword']);
    $AdminLevel = mysqli_real_escape_string($con, $_POST['AdminLevel']);
    $AdminStatus = mysqli_real_escape_string($con, $_POST['AdminStatus']);

    // Hash the password securely
    $hashedPassword = password_hash($AdminPassword, PASSWORD_DEFAULT);

    // Check if the record already exists
    $existingRecordQuery = mysqli_query($con, "SELECT * FROM admindata WHERE AdminUserName ='$AdminUserName'");
    
    if (mysqli_num_rows($existingRecordQuery) > 0) {
        // Update query
        $updateQuery = "UPDATE admindata 
                        SET AdminID = '$AdminID', 
                            AdminName = '$AdminName', 
                            AdminPassword = '$hashedPassword', 
                            AdminLevel = '$AdminLevel', 
                            AdminStatus = '$AdminStatus' 
                        WHERE AdminUserName = '$AdminUserName'";
        
        mysqli_query($con, $updateQuery);
        
        if (mysqli_affected_rows($con) > 0) {
            $error_message = "Update successful.";
        } else {
            $error_message = "Error updating record: " . mysqli_error($con);
        }
    } else {
        // Insert query
        $insertQuery = "INSERT INTO admindata (AdminID, AdminName, AdminUserName, AdminPassword, AdminLevel, AdminStatus) 
                        VALUES ('$AdminID', '$AdminName', '$AdminUserName', '$hashedPassword', '$AdminLevel', '$AdminStatus')";
        
        mysqli_query($con, $insertQuery);
        
        if (mysqli_affected_rows($con) > 0) {
            $error_message = "Insert successful.";
        } else {
            $error_message = "Error inserting record: " . mysqli_error($con);
        }
    }
}
?>
<div class="Search-Admin">
    <h1>Search Admin</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="AdminUserName">Admin Username:</label>
        <input type="text" id="AdminUserName" name="AdminUserName" value="<?php echo isset($_POST['AdminUserName']) ? $_POST['AdminUserName'] : ''; ?>" required>
        <input type="submit" name="search" value="Search">
    </form>
    <?php
        // Display error message if set
        if (!empty($error_search)) {
            echo "<div class='message error'>
                    <p>$error_search</p>
                </div><br>";
        }
    ?>
    <div class="Login">
        <p>Back to <a href="Admin-Page.php" class="login-button">Admin Page</a></p>
    </div>
</div>
<div class="SignUp">
    <h1>Admin Registration</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <label for="AdminID">Admin ID:</label>
        <input type="text" id="AdminID" name="AdminID" value="<?php echo isset($_POST['AdminID']) ? $_POST['AdminID'] : ''; ?>" required><br><br>

        <label for="AdminName">Admin Name:</label>
        <input type="text" id="AdminName" name="AdminName" value="<?php echo isset($_POST['AdminName']) ? $_POST['AdminName'] : ''; ?>" required><br><br>

        <label for="AdminUserName">Admin Username:</label>
        <input type="text" id="AdminUserName" name="AdminUserName" value="<?php echo isset($_POST['AdminUserName']) ? $_POST['AdminUserName'] : ''; ?>" required><br><br>

        <label for="AdminPassword">Admin Password:</label>
        <input type="password" id="AdminPassword" name="AdminPassword" value="<?php echo isset($_POST['AdminPassword']) ? $_POST['AdminPassword'] : ''; ?>" required><br><br>

        <label for="AdminLevel">Admin Level:</label>
        <select id="AdminLevel" name="AdminLevel" required>
            <option value="" <?php echo (!isset($_POST['AdminLevel']) || $_POST['AdminLevel'] == '') ? 'selected' : ''; ?> disabled>Select Admin Level</option>
            <option value="KING" <?php echo (isset($_POST['AdminLevel']) && $_POST['AdminLevel'] == 'KING') ? 'selected' : ''; ?> disabled>KING</option>
            <option value="Mapping Anggaran" <?php echo (isset($_POST['AdminLevel']) && $_POST['AdminLevel'] == 'Mapping Angggaran') ? 'selected' : ''; ?>>Mapping Anggaran</option>
            <option value="Verifikatur" <?php echo (isset($_POST['AdminLevel']) && $_POST['AdminLevel'] == 'Verifikatur') ? 'selected' : ''; ?>>Verifikatur</option>
            <option value="SPM" <?php echo (isset($_POST['AdminLevel']) && $_POST['AdminLevel'] == 'SPM') ? 'selected' : ''; ?>>SPM</option>
            <option value="Bendahara" <?php echo (isset($_POST['AdminLevel']) && $_POST['AdminLevel'] == 'Bendahara') ? 'selected' : ''; ?>>Bendahara</option>
        </select><br><br>
        
        <label for="AdminStatus">Admin Status:</label>
        <input type="text" id="AdminStatus" name="AdminStatus" value="<?php echo isset($_POST['AdminStatus']) ? $_POST['AdminStatus'] : ''; ?>" required><br><br>

        <input type="submit" name="submit" value="Sign Up">
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
        <p>Have an account? <a href="../Logout.php" class="login-button">Login</a></p>
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

    .SignUp, .Search-Admin {
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
    input[type="AdminLevel"],
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

    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
    }
</style>
</body>
</html>
