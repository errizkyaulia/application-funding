<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pengajuan</title>
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

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Handle form submission
            $NomorStJenisKeg = $_POST['NomorStJenisKeg'];
            $catatan = $_POST['catatan'];
            $TanggalPengajuan = $_POST['TanggalPengajuan'];
            $status = "diajukan";

            // Insert the transaction data into the database
            $query = "INSERT INTO transaksi (UserID, NomorStJenisKeg, catatan, TanggalPengajuan, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->bind_param("issss", $userId, $NomorStJenisKeg, $catatan, $TanggalPengajuan, $status);
            $stmt->execute();
            $stmt->close();

            // Redirect to the history page after successful submission
            header("Location: history.php");
            exit(); // Ensure that the script stops after the redirect
        }
    ?>
</head>
<body style="background-color: #f0f0f0; font-family: 'Arial', sans-serif;">

    <div class="form-container">
        <h2>Isi Form Dibawah ini</h2>
        <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data">

            <img src="<?php echo $imageSrc; ?>" alt="Profile Picture">
            
            <label for="fullName">Name:</label>
            <input type="text" id="fullName" name="fullName" value="<?php echo $fullName; ?>" readonly>
            
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" value="<?php echo $email; ?>" readonly>
            
            <label for="bidang">Bidang:</label>
            <input type="text" id="bidang" name="bidang" value="<?php echo $bidang; ?>" readonly>

            <label for="phoneNumber">Nomor Telepon:</label>
            <input type="text" id="phoneNumber" name="phoneNumber" value="<?php echo $phoneNumber; ?>" readonly>

            <label for="NomorStJenisKeg">Nomor ST / Jenis Kegunaan:</label>
            <input type="text" id="NomorStJenisKeg" name="NomorStJenisKeg" required>

            <label for="catatan">Catatan:</label>
            <textarea id="catatan" name="catatan" required></textarea>

            <label for="TanggalPengajuan">Tanggal dan Waktu Pengajuan:</label>
            <input type="datetime-local" id="TanggalPengajuan" name="TanggalPengajuan" required>
            
            <button type="submit">Submit</button>
        </form>

        <div class="Back-Home">
            <p>Back to <a href="Home.php" class="Back-button">Home</a></p>
        </div>
    </div>

    <style>
        /* Add your CSS styles for beautiful decoration here */
        h2 {
            text-align: center;
            color: #333;
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
        .form-container label {
            display: block;
            margin-bottom: 10px;
        }
        .form-container input[type="text"],
        .form-container textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        
        .form-container button {
            align-items: center;
            text-align: center;
            background-image: linear-gradient(144deg,#AF40FF, #5B42F3 50%,#00DDEB);
            border: 0;
            border-radius: 8px;
            box-shadow: rgba(151, 65, 252, 0.2) 0 15px 30px -5px;
            box-sizing: border-box;
            color: #FFFFFF;
            display: flex;
            font-family: Phantomsans, sans-serif;
            font-size: 18px;
            justify-content: center;
            line-height: 1em;
            max-width: 100%;
            min-width: 140px;
            padding: 3px;
            text-decoration: none;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            white-space: nowrap;
            cursor: pointer;
            transition: all .3s;
        }

        .form-container button:active,
        button:hover {
            outline: 0;
        }

        .form-container button span {
            background-color: rgb(5, 6, 45);
            padding: 16px 24px;
            border-radius: 6px;
            width: 100%;
            height: 100%;
            transition: 300ms;
        }

        .form-container button:hover span {
            background: none;
        }

        .form-container button:active {
            transform: scale(0.9);
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
</body>
</html>