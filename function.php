<?php
// Function to get current time on integer in Jakarta timezone
function getCurrentTime() {
    date_default_timezone_set('Asia/Jakarta');
    $time = time();
    return $time;
}

// Function to get the client's IP address
function getClientIp() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Function to display error message
function displayErrorMessage($error_message, &$count) {
    if (!empty($error_message)) {
        $count = $_SESSION['login_attempts']; //Counter for login attempts

        // Display error message and attempt count
        if ($count > 3) {
            $error_message = "Too many attempts. Please try again later
            <a href='" . BASE_URL . "forgot-password.php' class='forgot-password-button'>Forgot password?</a>";
            $waring_message = "";
        }

        echo "<div class='login-error-message'>
                <p>$error_message</p>
                <p>Attempt $count</p>
            </div><br>";
    }
}

// Function to log the login into the login_record database
function loginRecords($userid, $AdminID, $ip){
    // Log the login into the login_record database
    $stmt_login_record = mysqli_prepare($con, "INSERT INTO login_record (UserID, AdminID, ip_address, login_time) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_login_record, "iiss", $userid, $AdminID, $ip, date("Y-m-d H:i:s"));
    mysqli_stmt_execute($stmt_login_record);
}

// Function

?>