<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapping Anggaran</title>
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
    if (!isset($_SESSION['AdminLevel']) || ($_SESSION['AdminLevel'] !== 'KING' && $_SESSION['AdminLevel'] !== 'Mapping Anggaran')) {
        echo '<script>alert("You are not authorized to access this page");</script>';
        echo '<script>window.location.href = "Admin-Page.php";</script>';
        exit(); // Add this line to stop further execution if unauthorized
    }

    // Retrieve transactionID from the URL
    if (isset($_GET['TransactionID'])) {
        $selectedTransactionID = $_GET['TransactionID'];
        // Fetch combined data from both tables using a JOIN query
        $query = "SELECT t.TransaksiID, t.userid, t.TanggalPengajuan, t.NomorStJenisKeg, t.catatan, t.status, u.fullName, u.email, u.phoneNumber, u.bidang, u.PIC 
        FROM transaksi t
        JOIN userdata u ON t.userid = u.UserID
        WHERE t.TransaksiID = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $selectedTransactionID);
        $stmt->execute();
        $stmt->bind_result($TransaksiID, $userId, $TanggalPengajuan, $NomorStJenisKeg, $catatan, $status, $fullName, $email, $phoneNumber, $bidang, $PIC);

        // Display the transaction data in a table
        echo '<table border="1">
        <tr>
            <th>Transaction ID</th>
            <th>PIC</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Bidang</th>
            <th>Tanggal Pengajuan</th>
            <th>Nomor ST / Jenis Kegunaan</th>
            <th>Catatan</th>
            <th>Status</th>
        </tr>';
    } else {
        // Redirect to the transaction list page if the transactionID is not provided
        echo '<script>alert("Error: TransactionID not provided! Please return to the page to Select the Transaction");</script>';
        echo '<script>window.location.href = "Transaction-List.php";</script>';
    }

    ?>
</head>
<body>
    <div class="Transaction-List">
    <?php
        // Display the information
        if ($stmt->fetch()) {
            echo '<h1>Ini adalah Pengajuan Atas nama '. $fullName .'</h1>';
            // Display combined data in the table
            echo '<tr>
            <td>' . $TransaksiID . '</td>
            <td><img src="data:image/jpeg;base64,' . base64_encode($PIC) . '" alt="Profile Picture" width="50" height="50"></td>
            <td>' . $fullName . '</td>
            <td>' . $email . '</td>
            <td>' . $phoneNumber . '</td>
            <td>' . $bidang . '</td>
            <td>' . $TanggalPengajuan . '</td>
            <td>' . $NomorStJenisKeg . '</td>
            <td>' . $catatan . '</td>
            <td>' . $status . '</td>
            </tr>';
            echo '</table>';
        } else {
            echo '<p>Error: Transaction not found</p>';
        }
        $stmt->close();
    ?>
    </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the form is submitted
        if (isset($_POST['update'])) {
            // Check if data already exists
            $checkQuery = "SELECT COUNT(*) FROM pembebanan WHERE TransaksiID = ? AND AdminID = ?";
            $stmtCheck = $con->prepare($checkQuery);
            $stmtCheck->bind_param("ii", $TransaksiID, $AdminID);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                // Update the existing data
                $updateQuery = "UPDATE pembebanan SET Bisma=?, SumberDana=?, Akun=?, Detail=?, Anggaran=?, Realisasi=?, TotalRealisasi=?, Saldo=?, TanggalSelesaiPembebanan=? WHERE TransaksiID=?";
                $stmtUpdate = $con->prepare($updateQuery);
                $stmtUpdate->bind_param("ssssddddsi", $_POST['Bisma'], $_POST['SumberDana'], $_POST['Akun'], $_POST['Detail'], $_POST['Anggaran'], $_POST['Realisasi'], $_POST['TotalRealisasi'], $_POST['Saldo'], $_POST['TanggalSelesaiPembebanan'], $TransaksiID);

                if ($stmtUpdate->execute()) {
                    echo '<h1>Data updated successfully!</h1>';
                    // Redirect to other page to display the updated data
                    //echo '<script>window.location.href = "Transaction-List.php";</script>';
                } else {
                    echo '<h1>Error updating data:' . $stmtUpdate->error . '</h1>;';
                }
                $stmtUpdate->close();
            } else {
                // Check if PembebananID is already in use
                $checkPembebananIDQuery = "SELECT COUNT(*) FROM pembebanan WHERE PembebananID = ?";
                $stmtCheckPembebananID = $con->prepare($checkPembebananIDQuery);
                $stmtCheckPembebananID->bind_param("s", $_POST['PembebananID']);
                $stmtCheckPembebananID->execute();
                $stmtCheckPembebananID->bind_result($countPembebananID);
                $stmtCheckPembebananID->fetch();
                $stmtCheckPembebananID->close();

                if ($countPembebananID > 0) {
                    echo '<h1>Warning: PembebananID is already in use!</h1>';
                } else {
                    // Insert new data
                    $insertQuery = "INSERT INTO pembebanan (PembebananID, TransaksiID, AdminID, Bisma, SumberDana, Akun, Detail, Anggaran, Realisasi, TotalRealisasi, Saldo, TanggalSelesaiPembebanan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtInsert = $con->prepare($insertQuery);
                    $stmtInsert->bind_param("siisssssddds", $_POST['PembebananID'], $TransaksiID, $AdminID, $_POST['Bisma'], $_POST['SumberDana'], $_POST['Akun'], $_POST['Detail'], $_POST['Anggaran'], $_POST['Realisasi'], $_POST['TotalRealisasi'], $_POST['Saldo'], $_POST['TanggalSelesaiPembebanan']);

                    if ($stmtInsert->execute()) {
                        echo '<h1>Data inserted successfully!</h1>';
                        // You may redirect or perform any other action after the insert
                    } else {
                        echo '<h1>Error inserting data: '. $stmtInsert->error . '</h1>';
                    }
                    $stmtInsert->close();
                    // Update the status of the transaction
                    $UpdatePembebananStatus = "UPDATE transaksi Set status='Sudah Di Anggarkan' WHERE TransaksiID=?";
                    $stmtUpdatePembebananStatus = $con->prepare($UpdatePembebananStatus);
                    $stmtUpdatePembebananStatus->bind_param("i", $TransaksiID);
                    $stmtUpdatePembebananStatus->execute();
                    $stmtUpdatePembebananStatus->close();
                }
            }
        }
    }
    ?>
    <div class="form-container">
    <?php
    // Fetch data from the 'pembebanan' table
    $queryPembebanan = "SELECT PembebananID, Bisma, SumberDana, Akun, Detail, Anggaran, Realisasi, TotalRealisasi, Saldo, TanggalSelesaiPembebanan FROM pembebanan WHERE TransaksiID = ?";
    $stmtPembebanan = $con->prepare($queryPembebanan);
    $stmtPembebanan->bind_param("i", $TransaksiID);
    $stmtPembebanan->execute();
    $stmtPembebanan->bind_result($PembebananID, $Bisma, $SumberDana, $Akun, $Detail, $Anggaran, $Realisasi, $TotalRealisasi, $Saldo, $TanggalSelesaiPembebanan);
    $stmtPembebanan->fetch();
    // Display data from the 'pembebanan' table in a vertical table
    echo '<form method="post" action="">';

    echo '<table border="1">
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>';

    // Associative array to map field names to their corresponding values
    $data = array(
        'PembebananID' => $PembebananID,
        'Bisma' => $Bisma,
        'Sumber Dana' => $SumberDana,
        'Akun' => $Akun,
        'Detail' => $Detail,
        'Anggaran' => $Anggaran,
        'Realisasi' => $Realisasi,
        'Total Realisasi' => $TotalRealisasi,
        'Saldo' => $Saldo,
        'Tanggal Selesai Pembebanan' => $TanggalSelesaiPembebanan
    );

    foreach ($data as $field => $value) {
        // Use datetime-local input for specific fields
        if ($field == 'Tanggal Selesai Pembebanan') {
            echo '<tr>
                    <td>' . $field . '</td>
                    <td><input type="datetime-local" name="' . str_replace(' ', '', $field) . '" value="' . date('Y-m-d\TH:i', strtotime($value)) . '"></td>
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
    $stmtPembebanan->close();
    ?>
    </div>
    <div class="Back-Home">
        <p>Back to <a href="Transaction-List.php" class="Back-button">Transaction List</a></p>
    </div>
</body>
</html>