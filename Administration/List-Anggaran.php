<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Selesai Anggaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
            text-align: center;
            color: white;
        }

        .message {
            color: red;
            text-align: center;
            margin-top: 20px;
        }
        
        .proceed-button {
        display: inline-block;
        padding: 8px 12px;
        background-color: #4CAF50;
        color: white;
        text-align: center;
        text-decoration: none;
        font-size: 14px;
        cursor: pointer;
        border-radius: 4px;
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
    require_once 'connection-Admin.php';
    require_once "authenticate-Admin.php";
    ?>
</head>
<body>
    <h1>Daftar Transaksi yang Sudah di Anggarkan</h1>

    <form method="POST" action="">
        <label for="TransaksiID">Transaction ID:</label>
        <input type="text" name="TransaksiID" id="TransaksiID">
        <button type="submit">Search</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $TransaksiID = $_POST['TransaksiID'];

        $queryTransaksi = "SELECT TanggalPengajuan, NomorStJenisKeg, catatan, status FROM transaksi WHERE TransaksiID = ?";
        $stmtTransaksi = $con->prepare($queryTransaksi);
        $stmtTransaksi->bind_param("i", $TransaksiID);
        $stmtTransaksi->execute();
        $stmtTransaksi->bind_result($TanggalPengajuan, $NomorStJenisKeg, $catatan, $status);
        $stmtTransaksi->fetch();
        
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
            $stmtTransaksi->close();

            // Fetch data from the 'pembebanan' table
            $queryPembebanan = "SELECT PembebananID, Bisma, SumberDana, Akun, Detail, Anggaran, Realisasi, TotalRealisasi, Saldo, TanggalSelesaiPembebanan FROM pembebanan WHERE TransaksiID = ?";
            $stmtPembebanan = $con->prepare($queryPembebanan);
            $stmtPembebanan->bind_param("i", $TransaksiID);
            $stmtPembebanan->execute();
            $stmtPembebanan->bind_result($PembebananID, $Bisma, $SumberDana, $Akun, $Detail, $Anggaran, $Realisasi, $TotalRealisasi, $Saldo, $TanggalSelesaiPembebanan);
            $stmtPembebanan->fetch();
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
    <div class="Transaction-Data">
    <h2>Here is All of Yours Transaction Data</h2>
    <?php
    $sts = "Sudah Di Anggarkan";
    // Fetch combined data from both tables using a JOIN query
    $query = "SELECT t.TransaksiID, t.userid, t.TanggalPengajuan, t.NomorStJenisKeg, p.PembebananID, p.Bisma, p.SumberDana, p.Anggaran, p.TotalRealisasi, p.Saldo, t.catatan, t.status, u.fullName, u.bidang, u.PIC 
              FROM transaksi t
              JOIN userdata u ON t.userid = u.UserID
              JOIN pembebanan p ON t.TransaksiID = p.TransaksiID
              WHERE t.status = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $sts);
    $stmt->execute();
    $stmt->bind_result($TransaksiID, $userId, $TanggalPengajuan, $NomorStJenisKeg, $PembebananID, $Bisma, $SumberDana, $Anggaran, $TotalRealisasi, $Saldo, $catatan, $status, $fullName, $bidang, $PIC);

    // Display the transaction data in a table
    echo '<table border="1">
            <tr>
                <th>No</th>
                <th>Pembebanan ID</th>
                <th>PIC</th>
                <th>Full Name</th>
                <th>Bidang</th>
                <th>Tanggal Pengajuan</th>
                <th>Nomor ST / Jenis Kegunaan</th>
                <th>Catatan</th>
                <th>Bisma</th>
                <th>Sumber Dana</th>
                <th>Anggaran</th>
                <th>Total Realisasi</th>
                <th>Saldo</th>
                <th>Status</th>
                <th>Action</th> <!-- New column for buttons -->
            </tr>';

    // Fetch and display each row of data
    $count = 1;
    while ($stmt->fetch()) {
        // Display combined data in the table
        echo '<tr>
                <td>' . $count . '</td>
                <td>' . $PembebananID . '</td>
                <td><img src="data:image/jpeg;base64,' . base64_encode($PIC) . '" alt="Profile Picture" width="50" height="50"></td>
                <td>' . $fullName . '</td>
                <td>' . $bidang . '</td>
                <td>' . $TanggalPengajuan . '</td>
                <td>' . $NomorStJenisKeg . '</td>
                <td>' . $catatan . '</td>
                <td>' . $Bisma . '</td>
                <td>' . $SumberDana . '</td>
                <td>' . $Anggaran . '</td>
                <td>' . $TotalRealisasi . '</td>
                <td>' . $Saldo . '</td>
                <td>' . $status . '</td>
                <td><a class="proceed-button" href="Verifikatur.php?TransactionID=' . $TransaksiID . '">Pilih</a></td>
              </tr>';
        $count++;
    }

    echo '</table>';
    $stmt->close();
    ?>
    </div>
    <div class="Back-Home">
        <p>Back to <a href="Admin-Page.php" class="Back-button">Admin Page</a></p>
    </div>
</body>
</html>