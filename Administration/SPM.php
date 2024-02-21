<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPM</title>
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

        input[type="text"] {
            width: 100%;
            box-sizing: border-box;
            padding: 8px;
            margin: 4px 0;
        }

        input[type="submit"] {
            padding: 10px;
            text-align: center;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
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
    require_once 'connection-Admin.php';
    require_once "authenticate-Admin.php";

    // Check if the Admin is authorized to access this page
    if (!isset($_SESSION['AdminLevel']) || ($_SESSION['AdminLevel'] !== 'KING' && $_SESSION['AdminLevel'] !== 'SPM')) {
        echo '<script>alert("You are not authorized to access this page");</script>';
        echo '<script>window.location.href = "Admin-Page.php";</script>';
        exit(); // Add this line to stop further execution if unauthorized
    }

    // Retrieve transactionID from the URL
    if (isset($_GET['TransactionID'])) {
        $selectedTransactionID = $_GET['TransactionID'];
        // Fetch combined data from both tables using a JOIN query
        $query = "SELECT p.PembebananID, t.TransaksiID, t.userid, t.TanggalPengajuan, t.NomorStJenisKeg, p.Bisma, p.SumberDana, p.Akun, p.Detail, p.Anggaran, p.Realisasi, p.TotalRealisasi, p.Saldo, t.catatan, t.status, u.fullName, u.email, u.phoneNumber, u.bidang, u.PIC 
        FROM transaksi t
        JOIN userdata u ON t.userid = u.UserID
        JOIN pembebanan p ON t.TransaksiID = p.TransaksiID
        WHERE t.TransaksiID = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $selectedTransactionID);
        $stmt->execute();
        $stmt->bind_result($PembebananID, $TransaksiID, $userId, $TanggalPengajuan, $NomorStJenisKeg, $Bisma, $SumberDana, $Akun, $Detail, $Anggaran, $Realisasi, $TotalRealisasi, $Saldo, $catatan, $status, $fullName, $email, $phoneNumber, $bidang, $PIC);

        // Display the transaction data in a table
        echo '<table border="1">
        <tr>
            <th>Pembebanan ID</th>
            <th>PIC</th>
            <th>Full Name</th>
            <th>Bidang</th>
            <th>Tanggal Pengajuan</th>
            <th>Nomor ST / Jenis Kegunaan</th>
            <th>Catatan</th>
            <th>Status</th>
        </tr>';
    } else {
        echo '<script>alert("Error: TransactionID not provided! Please return to the page to Select the Transaction");</script>';
        echo '<script>window.location.href = "List-SPM.php";</script>';
        exit();
    }
    ?>
</head>
<body>
    <div class="Transaction-List">
    <?php
        // Display the information
        if ($stmt->fetch()) {
            echo '<h1>Ini adalah Data Pengajuan Atas Nama '. $fullName .'</h1>';
            // Display combined data in the table
            echo '<tr>
            <td>' . $PembebananID . '</td>
            <td><img src="data:image/jpeg;base64,' . base64_encode($PIC) . '" alt="Profile Picture" width="50" height="50"></td>
            <td>' . $fullName . '</td>
            <td>' . $bidang . '</td>
            <td>' . $TanggalPengajuan . '</td>
            <td>' . $NomorStJenisKeg . '</td>
            <td>' . $catatan . '</td>
            <td>' . $status . '</td>
            </tr>';
            echo '</table>';

            echo '<h2>Data Anggaran</h2>';
            echo '<table border="1">
            <tr>
                <th>Bisma</th>
                <th>Sumber Dana</th>
                <th>Akun</th>
                <th>Detail</th>
                <th>Anggaran</th>
                <th>Realisasi</th>
                <th>Total Realisasi</th>
                <th>Saldo</th>
            </tr>';

            echo '<tr>
                    <td>' . $Bisma . '</td>
                    <td>' . $SumberDana . '</td>
                    <td>' . $Akun . '</td>
                    <td>' . $Detail . '</td>
                    <td>' . $Anggaran . '</td>
                    <td>' . $Realisasi . '</td>
                    <td>' . $TotalRealisasi . '</td>
                    <td>' . $Saldo . '</td>
                  </tr>';
            echo '</table>';
        } else {
            echo '<p>Error: Transaction not found</p>';
        }
        $stmt->close();
    ?>
    </div>
    <div class="form-container">
    <?php
    // Fetch data from the 'verification' table
    $queryVerifikator = "SELECT VerificationID, TanggalSelesaiVerifikasi, TanggalSelesaiTTD, CatatanVerifikasi, UpdateStatusSPJ FROM verification WHERE TransaksiID = ?";
    $stmtVerifikator = $con->prepare($queryVerifikator);
    $stmtVerifikator->bind_param("i", $TransaksiID);
    $stmtVerifikator->execute();
    $stmtVerifikator->bind_result($VerificationID, $TanggalSelesaiVerifikasi, $TanggalSelesaiTTD, $CatatanVerifikasi, $UpdateStatusSPJ);
    $stmtVerifikator->fetch();

    // Display the data in a table
    echo '<h2><label for="VerificationID">Verification ID: </label>' . $VerificationID . '</h2>';
    echo '<table border="1">
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>';

    // Associative array to map field names to their corresponding values
    $data = array(
        'Tanggal Selesai Verifikasi' => $TanggalSelesaiVerifikasi,
        'Tanggal Selesai TTD' => $TanggalSelesaiTTD,
        'Catatan Verifikasi' => $CatatanVerifikasi,
        'Update Status SPJ' => $UpdateStatusSPJ,
    );

    foreach ($data as $field => $value) {
        // Use datetime-local input for specific fields
        if ($field == 'Tanggal Selesai Verifikasi' || $field == 'Tanggal Selesai TTD') {
            echo '<tr>
                    <td>' . $field . '</td>
                    <td><input type="datetime-local" name="' . $field . '" value="' . date('Y-m-d\TH:i', strtotime($value)) . '" disabled></td>
                </tr>';
        } else {
            echo '<tr>
                    <td>' . $field . '</td>
                    <td><input type="text" name="' . $field . '" value="' . $value . '" disabled></td>
                </tr>';
        }
    }
    
    echo '</table>';
    $stmtVerifikator->close();
    ?>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the form is submitted
        if (isset($_POST['update'])) {
            // Check if data already exists
            $checkQuery = "SELECT COUNT(*) FROM spm WHERE TransaksiID = ?";
            $stmtCheck = $con->prepare($checkQuery);
            $stmtCheck->bind_param("i", $TransaksiID);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                // Update the existing data
                $updateQuery = "UPDATE spm SET KetPengajuan=?, NomorSPM=?, TanggalSPM=?, CatatanBendahara=?, TanggalBayarBendahara=? WHERE TransaksiID=?";
                $stmtUpdate = $con->prepare($updateQuery);
                $stmtUpdate->bind_param("sssssi", $_POST['KetPengajuan'], $_POST['NomorSPM'], $_POST['TanggalSPM'], $_POST['CatatanBendahara'], $_POST['TanggalBayarBendahara'], $TransaksiID);

                if ($stmtUpdate->execute()) {
                    if ($status == 'SPM sudah diterbitkan') {
                        echo '<h1>SPM sudah diterbitkan, tidak bisa diubah lagi!</h1>';
                    } else {
                        // Update the status of the transaction
                        $statusUpdateQuery = "UPDATE transaksi SET status='SPM sudah diterbitkan' WHERE TransaksiID=?";
                        $stmtStatusUpdate = $con->prepare($statusUpdateQuery);
                        $stmtStatusUpdate->bind_param("i", $TransaksiID);
                        $stmtStatusUpdate->execute();
                        $stmtStatusUpdate->close();
                    }
                    // Redirect to SPM.php with a success parameter
                    echo '<script>window.location.href = "SPM.php?TransactionID=' . $TransaksiID . '&success=2";</script>';
                    exit();
                } else {
                    echo '<h1>Error updating data:' . $stmtUpdate->error . '</h1>;';
                }

                $stmtUpdate->close();
            } else {
                // Generate SPMID
                $SPMID = $_POST['NomorSPM'] . '/' . $AdminID . '/' . date('YmdHis');
                // Insert new data
                $insertQuery = "INSERT INTO spm (SPMID, TransaksiID, AdminID, KetPengajuan, NomorSPM, TanggalSPM, CatatanBendahara, TanggalBayarBendahara) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtInsert = $con->prepare($insertQuery);
                $stmtInsert->bind_param("siisssss", $SPMID, $TransaksiID, $AdminID, $_POST['KetPengajuan'], $_POST['NomorSPM'], $_POST['TanggalSPM'], $_POST['CatatanBendahara'], $_POST['TanggalBayarBendahara']);

                if ($stmtInsert->execute()) {
                    // Update the status of the transaction
                    $statusUpdateQuery = "UPDATE transaksi SET status='SPM sudah diterbitkan' WHERE TransaksiID=?";
                    $stmtStatusUpdate = $con->prepare($statusUpdateQuery);
                    $stmtStatusUpdate->bind_param("i", $TransaksiID);
                    $stmtStatusUpdate->execute();
                    $stmtStatusUpdate->close();

                    // Redirect to SPM.php with a success parameter
                    echo '<script>window.location.href = "SPM.php?TransactionID=' . $TransaksiID . '&success=1";</script>';
                    exit();
                } else {
                    echo '<h1>Error inserting data: '. $stmtInsert->error . '</h1>';
                }

                $stmtInsert->close();
            }
        }
    }
    ?>
    <div class="form-container">
    <?php
    // Query to fetch data from the 'SPM' table
    $querySPM = "SELECT * FROM spm WHERE TransaksiID = ?";
    $stmtSPM = $con->prepare($querySPM);
    $stmtSPM->bind_param("i", $TransaksiID);
    $stmtSPM->execute();
    $stmtSPM->bind_result($SPMID, $TransaksiID, $AdminID, $KetPengajuan, $NomorSPM, $TanggalSPM);
    $stmtSPM->fetch();

    // Display data from the 'SPM' table in a vertical table
    echo '<h2>Form SPM</h2>';
    echo '<h2><label for="AdminName">SPM Signature: </label>' . $AdminName . '</h2>';
    echo '<h2><label for="VerificationID">SPM ID: </label>' . $SPMID . '</h2>';

    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo '<h1>Data inserted successfully!</h1>';
    } else if (isset($_GET['success']) && $_GET['success'] == 2) {
        echo '<h1>Data updated successfully!</h1>';
    }

    echo '<form method="post" action="">';

    echo '<table border="1">
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>';

    // Associative array to map field names to their corresponding values
    $data = array(
        'SPMID' => $SPMID,
        'Ket Pengajuan' => $KetPengajuan,
        'Nomor SPM' => $NomorSPM,
        'Tanggal SPM' => $TanggalSPM,
    );

    foreach ($data as $field => $value) {
        // Use datetime-local input for specific fields
        if ($field == 'SPMID') {
            echo '<tr>
                    <td>' . $field . '</td>
                    <td><input type="text" name="' . str_replace(' ', '', $field) . '" value="' . $value . '" disabled></td>
                </tr>';
        } else if ($field == 'Tanggal SPM') {
            echo '<tr>
                    <td>' . $field . '</td>
                    <td><input type="datetime-local" name="' . str_replace(' ', '', $field) . '" value="' . date('Y-m-d\TH:i', strtotime($value)) . '" ></td>
                </tr>';
        } else {
            echo '<tr>
                    <td>' . $field . '</td>
                    <td><input type="text" name="' . str_replace(' ', '', $field) . '" value="' . $value . '"></td>
                </tr>';
        }
    }

    echo '</table>';
    echo '<input type="submit" name="update" value="Update">';
    echo '</form>';
    $stmtSPM->close();
?>
    </div>
    <div class="Back-Home">
        <p>Back to <a href="List-SPM.php" class="Back-button">List SPM</a></p>
    </div>
</body>
</html>