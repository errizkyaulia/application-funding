<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <?php
    // Start the session
    require 'connection.php';
    require 'function.php';
    ?>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="<?php echo BASE_URL; ?>style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <?php
    // Rate limit settings
    $max_attempts = 3;
    $lockout_time = 300; // seconds

    // Set 0 and 1
    $zero = 0;
    $one = 1;

    // Check IP Address on Database
    $ip = getClientIp();

    // Check if the IP address is already in the database
    $stmt = mysqli_prepare($con, "SELECT id_attempts, attempts, last_attempt_time, result FROM login_attempts WHERE ip_address = ? AND result = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "si", $ip, $zero);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    // Check if the IP address is already in the database
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $id_attempts, $attempts, $last_attempt_time, $result);
        mysqli_stmt_fetch($stmt);

        // Set id_attempts in the local session
        $local_session_id_attempts = $id_attempts;

        // Update the last attempt count and time
        $_SESSION['login_attempts'] = $attempts;
        $local_session_last_login_attempt_time = $last_attempt_time;

        // Calculate the elapsed time since the last attempt
        $elapsed_time = time() - $last_attempt_time;

        // Check if the lockout time has expired
        if ($elapsed_time < $lockout_time && $attempts >= $max_attempts) {
            $remaining_time = $lockout_time - $elapsed_time;

            // Display error message on the login page
            $error_message = "You cannot log in for " . floor($remaining_time / 60) . " minutes and " . $remaining_time % 60 . " seconds. Please try again later.";
            $_SESSION['error_message'] = $error_message;
            header("Location: " . BASE_URL . "Login.php");
            exit();
        }
    }

    // Session counter for login attempts
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        
    }

    // Check attempts counter
    if (isset($_SESSION['login_attempts'])) {
        $count = $_SESSION['login_attempts'];
    } else {
        $count = 0;
    }

    // Display error message if set
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        echo "<script>alert('$message');</script>";
        unset($_SESSION['error_message']); // Clear the error message after displaying
    }
    $error_message = "";

    // Check if the user has attempted to login more than 3 times
    if (isset($_SESSION['login_attempts'])) {
        $login_attempts = $_SESSION['login_attempts'];
        if ($login_attempts > 3) {
            // Redirect to forgot-password.php
            $_SESSION['error_message'] = "Your current attempts have exceeded the limit. Please reset your password if you have forgotten it.";
            header("Location: " . BASE_URL . "forgot-password.php");
            exit();
        }
    }

    // Handle login form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username_email = mysqli_real_escape_string($con, $_POST['username_email']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
    
        // Check in user database
        $stmt_user = mysqli_prepare($con, "SELECT * FROM userdata WHERE (username=? OR email=?) LIMIT 1");
        mysqli_stmt_bind_param($stmt_user, "ss", $username_email, $username_email);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
    
        // Check in admin database
        $stmt_admin = mysqli_prepare($con, "SELECT * FROM admindata WHERE AdminUserName=? LIMIT 1");
        mysqli_stmt_bind_param($stmt_admin, "s", $username_email);
        mysqli_stmt_execute($stmt_admin);
        $result_admin = mysqli_stmt_get_result($stmt_admin);
    
        // Check if the user exists in either database
        if (mysqli_num_rows($result_user) > 0) {
            $user = mysqli_fetch_assoc($result_user);
            $userid = $user['UserID'];

            // Check Account State
            $accountState = mysqli_query($con, "SELECT AccountState FROM userdata WHERE UserID='$userid'");
            $accountState = mysqli_fetch_assoc($accountState);
            $accountState = $accountState['AccountState'];

            if ($accountState == "Pending Activation") {
                // Show account activation message
                $_SESSION['error_message'] = "Your account is not activated. Please check your email for the activation link.";
                header("Location: " . BASE_URL . "Login.php");
                exit();
            } elseif ($accountState == "Deactivated") {
                // Show account deactivation message
                $_SESSION['error_message'] = "Your account is deactivated. Please contact the administrator.";
                header("Location: " . BASE_URL . "Login.php");
                exit();
            } elseif ($accountState == "Banned") {
                // Show account banned message
                $_SESSION['error_message'] = "Your account is banned. Please contact the administrator.";
                header("Location: " . BASE_URL . "Login.php");
                exit();
            } elseif ($accountState == "ACTIVE") {
                // Authenticate the user
                $user_type = 'user';
            } else {
                // Show account error message
                $_SESSION['error_message'] = "Your account is not activated. Please check your email for the activation link.";
                header("Location: " . BASE_URL . "Login.php");
                exit();
            }

        } elseif (mysqli_num_rows($result_admin) > 0) {
            $admin = mysqli_fetch_assoc($result_admin);
            $AdminID = $admin['AdminID'];
            $user_type = 'admin';
        } else {
            $error_message = "Invalid username or email";
            //Session adding counter for login attempts
            $_SESSION['login_attempts']++;

            // Adding Count for login attempts
            $count = $count++;

            // Log the failed login attempt into the database
            if (mysqli_stmt_num_rows($stmt) > 0) {
                // Update the login attempt
                $stmt_update = mysqli_prepare($con, "UPDATE login_attempts SET attempts = ?, last_attempt_time = ?, result = ? WHERE id_attempts = ?");
                mysqli_stmt_bind_param($stmt_update, "iiii", $count, time(), $local_session_id_attempts, $zero);
                mysqli_stmt_execute($stmt_update);
            } else {
                // Insert the login attempt
                $stmt_insert = mysqli_prepare($con, "INSERT INTO login_attempts (ip_address, attempts, last_attempt_time, result) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_insert, "siii", $ip, $count, time(), $zero);
                mysqli_stmt_execute($stmt_insert);
            }
        }
    
        // Authenticate the user based on the determined type
        if (isset($user_type)) {
            $auth_result = ($user_type === 'user') ? password_verify($password, $user['password']) : password_verify($password, $admin['AdminPassword']);
            
            if ($auth_result) {
                // Set session based on user type
                $_SESSION['user_type'] = $user_type;

                // Counter
                $count = $count + 1;

                // Log the successfull login attempt into the database
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    // Update the login attempt
                    $stmt_update = mysqli_prepare($con, "UPDATE login_attempts SET attempts = ?, last_attempt_time = ?, result = ? WHERE id_attempts = ?");
                    mysqli_stmt_bind_param($stmt_update, "iiii", $count, time(), $local_session_id_attempts, $one);
                    mysqli_stmt_execute($stmt_update);
                } else {
                    // Insert the login attempt
                    $stmt_insert = mysqli_prepare($con, "INSERT INTO login_attempts (ip_address, attempts, last_attempt_time, result) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_insert, "siii", $ip, $count, time(), $one);
                    mysqli_stmt_execute($stmt_insert);
                }
                
                // Assign the session based on the user type
                if ($user_type === 'user') {
                    $_SESSION['user'] = $userid;
                    $AdminID = 0;
                    loginRecords($userid, $AdminID, $ip);
                    header("Location: " . BASE_URL . "User/Home.php");
                } elseif ($user_type === 'admin') {
                    $_SESSION['admin'] = $AdminID;
                    $userid = 0;
                    loginRecords($userid, $AdminID, $ip);
                    header("Location: " . BASE_URL . "Administration/Admin-Page.php");
                }
                
                exit();
            } else {
                // Counter
                $count = $count + 1;
                $time = time();

                // Log Failed to authenticate
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    // Update the login attempt
                    $stmt_update = mysqli_prepare($con, "UPDATE login_attempts SET attempts = ?, last_attempt_time = ?, result = ? WHERE id_attempts = ?");
                    mysqli_stmt_bind_param($stmt_update, "iiii", $count, $time, $local_session_id_attempts, $zero);
                    mysqli_stmt_execute($stmt_update);
                } else {
                    // Insert the login attempt
                    $stmt_insert = mysqli_prepare($con, "INSERT INTO login_attempts (ip_address, attempts, last_attempt_time, result) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_insert, "siii", $ip, $count, $time, $zero);
                    mysqli_stmt_execute($stmt_insert);
                }
                $error_message = "Failed to authenticate. Please check your username/email and password.";
                //Session adding counter for login attempts
                $_SESSION['login_attempts']++;
            }
        }
    }    
    ?>
    <div class="login">
        <h1>Login</h1>
        <form action="<?php echo BASE_URL; ?>Login.php" method="post">
            <label for="username_email">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="username_email" placeholder="Username or Email" id="username_email" required>
            <label for="password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <input type="submit" value="Login">
        </form>
        <div class="forgot-password">
            <?php
            displayErrorMessage($error_message, $count);
            ?>
        </div>
    </div>
    <div class="signup">
        <p>Don't have an account? <a href="<?php echo BASE_URL; ?>SignUp.php" class="signup-button">Sign up</a></p>    
    </div>
</body>
</html>