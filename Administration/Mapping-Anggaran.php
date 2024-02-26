<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapping Anggaran</title>
    <link href="styleAdmin.css" rel="stylesheet" type="text/css">

    <!-- Include jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
    $(document).ready(function() {
        // Get the maximum width among all tables
        var maxWidth = Math.max.apply(null, $("table").map(function() {
        return $(this).outerWidth();
        }).get());

        // Set the container width to the maximum table width
        $(".container").width(maxWidth);
    });
    </script>
    <?php
    require_once 'connection-Admin.php';
    require_once "authenticate-Admin.php";

    // Check if the Admin is authorized to access this page
    if (!isset($_SESSION['AdminLevel']) || ($_SESSION['AdminLevel'] !== 'KING' && $_SESSION['AdminLevel'] !== 'Mapping Anggaran')) {
        echo '<script>alert("You are not authorized to access this page");</script>';
        echo '<script>window.location.href = "Admin-Page.php";</script>';
        exit(); // Add this line to stop further execution if unauthorized
    }

    // Check if the form is submitted
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
</head>
<body>
    <?php
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

        echo '<div class="container">';
        echo '<div class="Transaction-List">';
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
        echo '</div>';
        echo '</div>';

        echo '<div class="container">';
        echo '<div class="form-container">';
        echo '<h1>Form Mapping Anggaran</h1>';
        echo '<h2>Signature by: '. $_SESSION['AdminName'] .'</h2>';
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
        echo '<button type="update">Update</button>';
        echo '</form>';
        $stmtPembebanan->close();
        echo '</div>';
        echo '</div>';

        echo '<div class="Back-Home">
                <p>Back to <a href="Mapping-Anggaran.php" class="Back-button">List</a></p>
            </div>';
    } else {
        // Display the search form
        echo '<div class="container">';
        echo '<h1>Kolom Pencarian</h1>';
        echo '<form method="" action="" onsubmit="window.location.href = \'Mapping-Anggaran.php?TransactionID=\' + encodeURIComponent(document.getElementById(\'TransaksiID\').value); return false;">
            <label for="TransaksiID">Search by Transaksi ID:</label>
            <input type="text" name="TransaksiID" id="TransaksiID">
            <button type="submit">Search</button>
        </form>';
        echo '</div>';

        echo '<div class="container">';
        echo '<div class="Transaction-Data">
        <h2>Here is All of Yours Transaction Data</h2>';
        $sts = "diajukan";
        // Fetch combined data from both tables using a JOIN query
        $query = "SELECT t.TransaksiID, t.userid, t.TanggalPengajuan, t.NomorStJenisKeg, t.catatan, t.status, u.fullName, u.email, u.phoneNumber, u.bidang, u.PIC 
                FROM transaksi t
                JOIN userdata u ON t.userid = u.UserID
                WHERE t.status = ?";
        
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $sts);
        $stmt->execute();
        $stmt->bind_result($TransaksiID, $userId, $TanggalPengajuan, $NomorStJenisKeg, $catatan, $status, $fullName, $email, $phoneNumber, $bidang, $PIC);

        // Display the transaction data in a table
        echo '<table border="1">
                <tr>
                    <th>No</th>
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
                    <th>Action</th>
                </tr>';

        // Fetch and display each row of data
        $count = 1;
        while ($stmt->fetch()) {
            // Display combined data in the table
            echo '<tr>
                    <td>' . $count . '</td>
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
                    <td><a class="proceed-button" href="Mapping-Anggaran.php?TransactionID=' . $TransaksiID . '">Pilih</a></td> <!-- Styled link for each row -->
                </tr>';
            $count++;
        }

        echo '</table>';
        $stmt->close();
        echo '</div>';
        echo '</div>';

        echo '<div class="Back-Home">
            <p>Back to <a href="Admin-Page.php" class="Back-button">Admin Page</a></p>
        </div>';
    }
    ?>
</body>
</html>