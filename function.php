<?php
    require_once 'connection.php';
    require_once 'authenticate.php';
    
    // Retrieve user information from the database
    $userInfo = getUserInfo($userid);
    $userFullName = getUserFullName($userid);

    // Function to get user ID from the username
    function getuserid($username)
    {
        global $con;

        // Adjust the query based on your database schema
        $query = "SELECT userid FROM userdata WHERE username = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($userid);

        // Fetch user ID
        $stmt->fetch();

        // Close the statement
        $stmt->close();

        return $userid;
    }

    // Function to get user information from the database
    function getUserInfo($userid)
    {
        global $con;

        // Adjust the query based on your database schema
        $query = "SELECT * FROM userdata WHERE userid = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $userid);
        $stmt->execute();

        // Get result set
        $result = $stmt->get_result();

        // Fetch user information as an associative array
        $userInfo = $result->fetch_assoc();

        // Close the statement
        $stmt->close();

        return $userInfo;
    }

    // Function to get user full name
    function getUserFullName($userid)
    {
        global $con;

        // Adjust the query based on your database schema
        $query = "SELECT fullName FROM userdata WHERE userid = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $stmt->bind_result($fullName);

        // Fetch user full name
        $stmt->fetch();

        // Close the statement
        $stmt->close();

        return $fullName;
    }
?>