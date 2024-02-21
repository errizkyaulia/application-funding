<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Editor</title>
    <style>
        h1 {
            color: #007BFF;
            text-align: center;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #007BFF;
        }

        .form-container {
            background-color: #cccccc;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 20px auto;
        }
        .form-container img{
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: block;
            margin: 0 auto;
            margin-bottom: 20px;
        }
        
        form {
            max-width: 500px;
            margin: 0 auto;
            background-color: #e6e6e6;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .Back-Home {
            margin-top: 20px;
            text-align: center;
        }

        .Back-button {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }

        .Back-button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    require_once 'connection.php';
    require_once 'authenticate.php';

    // Fetch data from the database
    $userId = $_SESSION['user'];
    $query = "SELECT fullName, email, phoneNumber, bidang, PIC FROM userdata WHERE UserID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($fullName, $email, $phoneNumber, $bidang, $PIC);
    $stmt->fetch();
    $stmt->close();

    // Assuming $PIC contains the binary data of the JPEG image
    if (!empty($PIC)) {
        $base64Image = base64_encode($PIC);
        $imageSrc = "data:image/jpeg;base64," . $base64Image;
    } else {
        // Default image path or URL
        $defaultImageSrc = "image/profile.png";
        $imageSrc = $defaultImageSrc;
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve updated profile data from the form
        $fullName = $_POST["fullName"];
        $phoneNumber = $_POST["phoneNumber"];
        $email = $_POST["email"];
        $bidang = $_POST["bidang"];
        $PIC = $_POST["PIC"];

        // Update the user profile in the database or any other data source
        $query = "UPDATE userdata SET fullName = ?, email = ?, phoneNumber = ?, bidang = ? WHERE UserID = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ssssi", $fullName, $email, $phoneNumber, $bidang, $userId);
        $stmt->execute();
        $stmt->close();

        // Handle profile picture upload
        if ($_FILES['profilePicture']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['profilePicture']['tmp_name'];
            $profilePictureData = file_get_contents($tmp_name);

            // Update the user profile picture blob in the database
            $query = "UPDATE userdata SET PIC = ? WHERE UserID = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("bi", $profilePictureData, $userId);
            $stmt->send_long_data(0, $profilePictureData);
            $stmt->execute();
            $stmt->close();
        }

        // Redirect to the profile page after successful update
        header("Location: profile.php");
        exit;
    }
    ?>

    
    <div class="form-container">
        <h1>Profile Editor</h1>
        <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data">
            <label for="fullName">Nama Lengkap:</label>
            <input type="text" name="fullName" value="<?php echo $fullName; ?>"><br>

            <label for="phoneNumber">Nomor Telepon:</label>
            <input type="text" name="phoneNumber" value="<?php echo $phoneNumber; ?>"><br>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo $email; ?>"><br>

            <label for="bidang">Bidang:</label><br>
            <input type="text" name="bidang" value="<?php echo $bidang; ?>"><br>

            <label for="profilePicture">Profile Picture:</label>
            <input type="file" name="profilePicture" accept="image/*">

            <img src="<?php echo $imageSrc; ?>" alt="Profile Picture">
            <input type="submit" value="Save">
        </form>
        <div class="Back-Home">
            <p>Back to <a href="Home.php" class="Back-button">Home</a></p>
        </div>
    </div>
</body>
</html>