<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikatur</title>
    <link href="styleAdmin.css" rel="stylesheet" type="text/css">
    <?php
    require_once 'connection-Admin.php';
    require_once "authenticate-Admin.php";

    // Check if the Admin is authorized to access this page
    if (!isset($_SESSION['AdminLevel']) || ($_SESSION['AdminLevel'] !== 'KING' && $_SESSION['AdminLevel'] !== 'Verifikatur')) {
        echo '<script>alert("You are not authorized to access this page");</script>';
        echo '<script>window.location.href = "Admin-Page.php";</script>';
        exit(); // Add this line to stop further execution if unauthorized
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the form is submitted
        if (isset($_POST['update'])) {
            // Check if data already exists
            $checkQuery = "SELECT COUNT(*) FROM verification WHERE TransaksiID = ?";
            $stmtCheck = $con->prepare($checkQuery);
            $stmtCheck->bind_param("i", $TransaksiID);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                // Update the existing data
                $updateQuery = "UPDATE verification SET VerificationID=? AdminID=? TanggalSelesaiVerifikasi=?, TanggalSelesaiTTD=?, CatatanVerifikasi=?, UpdateStatusSPJ=? WHERE TransaksiID=?";
                $stmtUpdate = $con->prepare($updateQuery);
                $stmtUpdate->bind_param("sissssi", $VerificationID, $AdminID, $_POST['TanggalSelesaiVerifikasi'], $_POST['TanggalSelesaiTTD'], $_POST['CatatanVerifikasi'], $_POST['UpdateStatusSPJ'], $TransaksiID);

                if ($stmtUpdate->execute()) {
                    echo '<h1>Data updated successfully!</h1>';
                    // Redirect to other page to display the updated data
                    //echo '<script>window.location.href = "List-Anggaran.php";</script>';
                } else {
                    echo '<h1>Error updating data:' . $stmtUpdate->error . '</h1>;';
                }

                $stmtUpdate->close();
            } else {
                // Insert new data
                $insertQuery = "INSERT INTO verification (VerificationID, PembebananID, TransaksiID, AdminID, TanggalSelesaiVerifikasi, TanggalSelesaiTTD, CatatanVerifikasi, UpdateStatusSPJ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtInsert = $con->prepare($insertQuery);
                $stmtInsert->bind_param("ssiissss", $VerificationID, $PembebananID, $TransaksiID, $AdminID, $_POST['TanggalSelesaiVerifikasi'], $_POST['TanggalSelesaiTTD'], $_POST['CatatanVerifikasi'], $_POST['UpdateStatusSPJ']);

                if ($stmtInsert->execute()) {
                    echo '<h1>Data inserted successfully!</h1>';
                    $statusUpdateQuery = "UPDATE transaksi SET status='Sudah Di Verifikasi' WHERE TransaksiID=?";
                    $stmtStatusUpdate = $con->prepare($statusUpdateQuery);
                    $stmtStatusUpdate->bind_param("i", $TransaksiID);
                    $stmtStatusUpdate->execute();
                    $stmtStatusUpdate->close();
                    // You may redirect or perform any other action after the insert
                } else {
                    echo '<h1>Error inserting data: '. $stmtInsert->error . '</h1>';
                }

                $stmtInsert->close();
            }
        }
    }
    ?>
</head>
<body>
    <div class="Transaction-List">
    <?php
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

        echo '<div class="container">';
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
        echo '</div>';
        // Setting VerificationID
        $VerificationID = $PembebananID . '/' . $AdminID;

        echo '<div class="form-container">';
        echo '<div class="container">';
        // Fetch data from the 'verification' table
        $queryVerifikator = "SELECT TanggalDiserahkan, TanggalSelesaiVerifikasi, TanggalSelesaiTTD, CatatanVerifikasi, UpdateStatusSPJ FROM verification WHERE TransaksiID = ?";
        $stmtVerifikator = $con->prepare($queryVerifikator);
        $stmtVerifikator->bind_param("i", $TransaksiID);
        $stmtVerifikator->execute();
        $stmtVerifikator->bind_result($TanggalDiserahkan, $TanggalSelesaiVerifikasi, $TanggalSelesaiTTD, $CatatanVerifikasi, $UpdateStatusSPJ);
        $stmtVerifikator->fetch();

        // Display data from the 'verification' table in a vertical table
        echo '<h2>Form Verifikatur</h2>';
        // Display Admin Name in the form
        echo '<h2><label for="AdminName">Verifikatur Signature: </label>' . $AdminName . '</h2>';
        echo '<h2><label for="VerificationID">Verification ID: </label>' . $VerificationID . '</h2>';

        // Display the form
        echo '<form method="post" action="">';

        echo '<table border="1">
                <tr>
                    <th>Field</th>
                    <th>Existing Data</th>
                    <th>New Data</th>
                </tr>';

        // Associative array to map field names to their corresponding values
        $fields = array(
            'Tanggal Diserahkan' => $TanggalDiserahkan,
            'Tanggal Selesai Verifikasi' => $TanggalSelesaiVerifikasi,
            'Tanggal Selesai TTD' => $TanggalSelesaiTTD,
        );

        foreach ($fields as $field => $existingData) {
            // Use datetime-local input for specific fields
            echo '<tr>
                    <td>' . $field . '</td>
                    <td>' . $existingData . '</td>
                    <td><input type="datetime-local" name="' . str_replace(' ', '', $field) . '" value="' . ($existingData ? date('Y-m-d\TH:i', strtotime($existingData)) : '') . '"></td>
                </tr>';
        }
        echo '</table>';
        
        // Add the Catatan Verifikasi and Update Status SPJ fields
        echo '<table border="1">
                <tr>
                    <th>Catatan Verifikasi</th>
                    <th>Update Status SPJ</th>
                </tr>';
        echo '<tr>
                <td><input type="text" name="CatatanVerifikasi" value="' . $CatatanVerifikasi . '"></td>
                <td><input type="text" name="UpdateStatusSPJ" value="' . $UpdateStatusSPJ . '"></td>
            </tr>';
        echo '</table>';
        echo '<input type="submit" name="update" value="Update">';
        echo '</form>';
        $stmtVerifikator->close();
        echo '</div>';
        echo '</div>';

        echo '<div class="Back-Home">
                <p>Back to <a href="Verifikatur.php" class="Back-button">List</a></p>
            </div>';
    } else {
        // Display the search form
        echo '<div class="container">';
        echo '<h1>Kolom Pencarian</h1>';
        echo '<form method="" action="" onsubmit="window.location.href = \'Verifikatur.php?TransactionID=\' + encodeURIComponent(document.getElementById(\'TransaksiID\').value); return false;">
            <label for="TransaksiID">Search by Transaksi ID:</label>
            <input type="text" name="TransaksiID" id="TransaksiID">
            <button type="submit">Search</button>
        </form>';
        echo '<p>*On develop</p>';
        echo '</div>';
        echo '<div class="container">';
        echo '<div class="Transaction-Data">
        <h2>Here is All of Yours Transaction Data</h2>';
        // Set the status to 'Sudah Di Anggarkan'
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
        echo '</div>';
        echo '</div>';

        // Add a back button to return to the Admin Page
        echo '<div class="Back-Home">
            <p>Back to <a href="Admin-Page.php" class="Back-button">Admin Page</a></p>
        </div>';
    }
    ?>
    </div>
</body>
</html>