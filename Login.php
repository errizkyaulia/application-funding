<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="https://er-apps.alwaysdata.net/style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <?php
    
    require_once 'connection.php';

    // Session counter for login attempts
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        
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
            header("Location: https://er-apps.alwaysdata.net/forgot-password.php");
            exit();
        }
    }


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
                header("Location: https://er-apps.alwaysdata.net/Login.php");
                exit();
            } elseif ($accountState == "Deactivated") {
                // Show account deactivation message
                $_SESSION['error_message'] = "Your account is deactivated. Please contact the administrator.";
                header("Location: https://er-apps.alwaysdata.net/Login.php");
                exit();
            } elseif ($accountState == "Banned") {
                // Show account banned message
                $_SESSION['error_message'] = "Your account is banned. Please contact the administrator.";
                header("Location: https://er-apps.alwaysdata.net/Login.php");
                exit();
            } elseif ($accountState == "ACTIVE") {
                // Authenticate the user
                $user_type = 'user';
            } else {
                // Show account error message
                $_SESSION['error_message'] = "Your account is not activated. Please check your email for the activation link.";
                header("Location: https://er-apps.alwaysdata.net/Login.php");
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
        }
    
        // Authenticate the user based on the determined type
        if (isset($user_type)) {
            $auth_result = ($user_type === 'user') ? password_verify($password, $user['password']) : password_verify($password, $admin['AdminPassword']);
            
            if ($auth_result) {
                // Set session based on user type
                $_SESSION['user_type'] = $user_type;
                
                if ($user_type === 'user') {
                    $_SESSION['user'] = $userid;
                    header("Location: https://er-apps.alwaysdata.net/User/Home.php");
                } elseif ($user_type === 'admin') {
                    $_SESSION['admin'] = $AdminID;
                    header("Location: https://er-apps.alwaysdata.net/Administration/Admin-Page.php");
                }
                exit();
            } else {
                // Failed to authenticate
                $error_message = "Failed to authenticate. Please check your username/email and password.";
                //Session adding counter for login attempts
                $_SESSION['login_attempts']++;
            }
        }
    }    
    ?>
    <div class="login">
        <h1>Login</h1>
        <form action="https://er-apps.alwaysdata.net/Login.php" method="post">
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
            function displayErrorMessage($error_message, &$count) {
                if (!empty($error_message)) {
                    $count = $_SESSION['login_attempts']; //Counter for login attempts

                    // Display error message and attempt count
                    if ($count > 3) {
                        $error_message = "Too many attempts. Please try again later
                        <a href='https://er-apps.alwaysdata.net/forgot-password.php' class='forgot-password-button'>Forgot password?</a>";
                        $waring_message = "";
                    }

                    echo "<div class='login-error-message'>
                            <p>$error_message</p>
                            <p>Attempt $count</p>
                        </div><br>";
                }
            }
            ?>
        </div>
    </div>
    <div class="signup">
        <p>Don't have an account? <a href="https://er-apps.alwaysdata.net/SignUp.php" class="signup-button">Sign up</a></p>    
    </div>
</body>
</html>