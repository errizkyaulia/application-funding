<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pengajuan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #cccccc;
            margin: 0;
            padding: 0;
        }

        h1, h2, p {
            text-align: center;
            color: #333;
        }

        form {
            text-align: center;
            margin-top: 20px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"] {
            padding: 8px;
            margin: 5px;
            width: 200px;
        }

        button {
            padding: 10px;
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4caf50;
            color: white;
        }

        .message {
            color: red;
            text-align: center;
            margin-top: 20px;
        }

        .Back-Home {
            text-align: center;
            margin-top: 20px;
        }

        .Back-button {
            color: #4caf50;
            text-decoration: none;
        }

        .Back-button:hover {
            text-decoration: underline;
        }
    </style>
    <?php
    require_once 'connection.php';
    require_once 'authenticate.php';
    
    // Get user ID from the username
    $userId = $_SESSION['user'];
    
    // Fetch user data from the database
    $query = "SELECT fullName, email, phoneNumber, bidang, PIC FROM userdata WHERE UserID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($fullName, $email, $phoneNumber, $bidang, $PIC);
    $stmt->fetch();
    $stmt->close();
    ?>
</head>
<body>
    <h1>Silahkan masukkan Nomor Transaksi Anda, <?php echo $fullName; ?></h1>

    <form method="POST" action="">
        <label for="TransaksiID">Transaction ID:</label>
        <input type="text" name="TransaksiID" id="TransaksiID">
        <button type="submit">Search</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $TransaksiID = $_POST['TransaksiID'];

        // Fetch data from the 'transaksi' table
        $queryTransaksi = "SELECT TanggalPengajuan, NomorStJenisKeg, catatan, status FROM transaksi WHERE UserID = ? AND TransaksiID = ?";
        $stmtTransaksi = $con->prepare($queryTransaksi);
        $stmtTransaksi->bind_param("ii", $userId, $TransaksiID);
        $stmtTransaksi->execute();
        $stmtTransaksi->bind_result($TanggalPengajuan, $NomorStJenisKeg, $catatan, $status);
        $stmtTransaksi->fetch();
        $stmtTransaksi->close();

        if (!empty($status)) {
            echo "<p>This is what you are looking for</p>";
            // Display data from the 'transaksi' table in a table
            echo '<table border="1">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Nomor ST / Jenis Kegunaan</th>
                        <th>Catatan</th>
                        <th>Status</th>
                    </tr>';
            echo '<tr>
                    <td>' . $TransaksiID . '</td>
                    <td>' . $TanggalPengajuan . '</td>
                    <td>' . $NomorStJenisKeg . '</td>
                    <td>' . $catatan . '</td>
                    <td>' . $status . '</td>
                  </tr>';
            echo '</table>';

            // Fetch data from the 'pembebanan' table
            $queryPembebanan = "SELECT PembebananID, Bisma, SumberDana, Akun, Detail, Anggaran, Realisasi, TotalRealisasi, Saldo, TanggalSelesaiPembebanan FROM pembebanan WHERE TransaksiID = ?";
            $stmtPembebanan = $con->prepare($queryPembebanan);
            $stmtPembebanan->bind_param("i", $TransaksiID);
            $stmtPembebanan->execute();
            $stmtPembebanan->bind_result($PembebananID, $Bisma, $SumberDana, $Akun, $Detail, $Anggaran, $Realisasi, $TotalRealisasi, $Saldo, $TanggalSelesaiPembebanan);

            if ($stmtPembebanan->fetch()) {
            // Display data from the 'pembebanan' table in a table
            echo '<table border="1">
                    <tr>
                        <th>Pembebanan ID</th>
                        <th>Bisma</th>
                        <th>Sumber Dana</th>
                        <th>Akun</th>
                        <th>Detail</th>
                        <th>Anggaran</th>
                        <th>Realisasi</th>
                        <th>Total Realisasi</th>
                        <th>Saldo</th>
                        <th>Tanggal Selesai Pembebanan</th>
                    </tr>';

            echo '<tr>
                    <td>' . $PembebananID . '</td>
                    <td>' . $Bisma . '</td>
                    <td>' . $SumberDana . '</td>
                    <td>' . $Akun . '</td>
                    <td>' . $Detail . '</td>
                    <td>' . $Anggaran . '</td>
                    <td>' . $Realisasi . '</td>
                    <td>' . $TotalRealisasi . '</td>
                    <td>' . $Saldo . '</td>
                    <td>' . $TanggalSelesaiPembebanan . '</td>
                  </tr>';
            echo '</table>';
            } else {
                $error_message = "Data Belum di Anggarkan</p>";
            }
            $stmtPembebanan->close();
        } else {
            $error_message = "Invalid Transaction ID";
        }

        // Display error message if set
        if (!empty($error_message)) {
            echo "<div class='message error'>
                    <p>$error_message</p>
                </div><br>";
        }
    }
    ?>
    <div class="Back-Home">
        <p>Back to <a href="Home.php" class="Back-button">Home</a></p>
    </div>
</body>
</html>