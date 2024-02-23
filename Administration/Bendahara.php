<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bendahara</title>
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
    if (!isset($_SESSION['AdminLevel']) || ($_SESSION['AdminLevel'] !== 'KING' && $_SESSION['AdminLevel'] !== 'Bendahara')) {
        echo '<script>alert("You are not authorized to access this page");</script>';
        echo '<script>window.location.href = "Admin-Page.php";</script>';
        exit(); // Add this line to stop further execution if unauthorized
    }
    // Get the current AdminID
    $CurrentAdminID = $_SESSION['admin'];
    $CurrentAdminName = $_SESSION['AdminName'];
    ?>
</head>
<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_GET['TransactionID'])) {
            // Retrieve the transactionID from the URL
            $selectedTransactionID = $_GET['TransactionID'];
            
            // Handle profile picture upload
            if ($_FILES['PICSelesaiTransfer']['error'] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['PICSelesaiTransfer']['tmp_name']; // Fix typo here
                $PICTf = file_get_contents($tmp_name);
    
                // Update the user profile picture blob in the database
                $query = "UPDATE bendahara SET PICSelesaiTransfer = ? WHERE TransaksiID = ?";
                $stmt = $con->prepare($query);
                $stmt->bind_param("bi", $PICTf, $selectedTransactionID);
                $stmt->send_long_data(0, $PICTf);
                $stmt->execute();
                $stmt->close();
            }
    
            // Check if the transaction data is already there
            $queryCek = "SELECT COUNT(*) FROM bendahara WHERE TransaksiID = ?";
            $stmtCek = $con->prepare($queryCek);
            $stmtCek->bind_param("i", $selectedTransactionID);
            $stmtCek->execute();
            $stmtCek->bind_result($cekTransaksiID);
            $stmtCek->fetch();
            $stmtCek->close();
    
            if ($cekTransaksiID > 0) {
                // Update the transaction data
                $queryUpBen = "UPDATE bendahara SET CatatanBendahara = ?, TanggalBayarBendahara = ?, PICSelesaiTransfer = ? WHERE TransaksiID = ?";
                $stmtUpBen = $con->prepare($queryUpBen);
                $stmtUpBen->bind_param("sssi", $_POST['CatatanBendahara'], $_POST['TanggalBayarBendahara'], $PICTf, $selectedTransactionID);
                $stmtUpBen->execute();
                $stmtUpBen->close();
                
                // Update the transaction status
                $queryUpTrans = "UPDATE transaksi SET status = 'Selesai Transfer' WHERE TransaksiID = ?";
                $stmtUpTrans = $con->prepare($queryUpTrans);
                $stmtUpTrans->bind_param("i", $selectedTransactionID);
                $stmtUpTrans->execute();
                $stmtUpTrans->close();
                
                // Redirect to Bendahara Page with a success parameter
                echo '<script>window.location.href = "Bendahara.php?TransactionID=' . $selectedTransactionID . '&success=2";</script>';
    
            } else {
                // Generate BendaharaID
                $BendaharaID = "BEN" . $CurrentAdminID . '/' . date("Ymd") . '/' . $selectedTransactionID;
    
                // Insert the transaction data
                $queryInsBen = "INSERT INTO bendahara (BendaharaID, TransaksiID, AdminID, CatatanBendahara, TanggalBayarBendahara, PICSelesaiTransfer) VALUES (?, ?, ?, ?, ?, ?)";
                $stmtInsBen = $con->prepare($queryInsBen);
                $stmtInsBen->bind_param("siisss", $BendaharaID, $selectedTransactionID, $CurrentAdminID, $_POST['CatatanBendahara'], $_POST['TanggalBayarBendahara'], $PICTf);
                $stmtInsBen->execute();
                $stmtInsBen->close();
    
                // Update the transaction status
                $queryUpTrans = "UPDATE transaksi SET status = 'Selesai Transfer' WHERE TransaksiID = ?";
                $stmtUpTrans = $con->prepare($queryUpTrans);
                $stmtUpTrans->bind_param("i", $selectedTransactionID);
                $stmtUpTrans->execute();
                $stmtUpTrans->close();
    
                // Redirect to Bendahara Page with a success parameter
                echo '<script>window.location.href = "Bendahara.php?TransactionID=' . $selectedTransactionID . '&success=1";</script>';
            }
    
        } else {
            // Redirect to Bendahara Page
            header("Location: Bendahara.php");
        }
    }    
    ?>
    </div>
    <div class="Bendahara-Action">
    <?php
    // Retrieve transactionID from the URL
    if (isset($_GET['TransactionID'])) {
        $selectedTransactionID = $_GET['TransactionID'];
        // Fetch combined data from both tables using a JOIN query
        $query = "SELECT a.AdminName, p.PembebananID, t.TransaksiID, t.userid, t.TanggalPengajuan, t.NomorStJenisKeg, p.Bisma, p.SumberDana, p.Akun, p.Detail, p.Anggaran, p.Realisasi, p.TotalRealisasi, p.Saldo, t.catatan, t.status, u.fullName, u.email, u.phoneNumber, u.bidang, u.PIC, v.TanggalSelesaiVerifikasi, v.TanggalSelesaiTTD, v.CatatanVerifikasi, v.UpdateStatusSPJ, s.NomorSPM, s.KetPengajuan, s.TanggalSPM 
        FROM transaksi t
        JOIN userdata u ON t.userid = u.UserID
        JOIN pembebanan p ON t.TransaksiID = p.TransaksiID
        JOIN verification v ON t.TransaksiID = v.TransaksiID
        JOIN admindata a ON v.AdminID = a.AdminID
        JOIN spm s ON t.TransaksiID = s.TransaksiID
        WHERE t.TransaksiID = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $selectedTransactionID);
        $stmt->execute();
        $stmt->bind_result($AdminName, $PembebananID, $TransaksiID, $userId, $TanggalPengajuan, $NomorStJenisKeg, $Bisma, $SumberDana, $Akun, $Detail, $Anggaran, $Realisasi, $TotalRealisasi, $Saldo, $catatan, $status, $fullName, $email, $phoneNumber, $bidang, $PIC, $TanggalSelesaiVerifikasi, $TanggalSelesaiTTD, $CatatanVerifikasi, $UpdateStatusSPJ, $NomorSPM, $KetPengajuan, $TanggalSPM);

        // Display the information
        if ($stmt->fetch()) {
            echo '<div class="container">';
            echo '<h1>Data Pengajuan Atas Nama '. $fullName .'</h1>';
            echo '<h2>Dengan Nomor SPM: '. $NomorSPM .'</h2>';

            echo '<table border="1">
            <tr>
                <th>Keterangan Pengajuan</th>
                <th>Tanggal Keluar SPM</th>
            </tr>';
            echo '<tr>
                <td>' . $KetPengajuan . '</td>
                <td>' . $TanggalSPM . '</td>
            </tr>';
            echo '</table>';
            
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
            echo '</div>';

            // Display the transaction data in a table
            echo '<div class="container">';
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
            echo '</div>';

            // verification data
            echo '<div class="container">';
            echo '<h2>Diverifikasi Oleh: '. $AdminName .'</h2>';
            echo '<table border="1">
                <tr>
                    <th>Tanggal Selesai Verifikasi</th>
                    <th>Tanggal Selesai TTD</th>
                    <th>Catatan Verifikasi</th>
                    <th>Status SPJ</th>
                </tr>';
            echo '<tr>
                    <td>' . $TanggalSelesaiVerifikasi . '</td>
                    <td>' . $TanggalSelesaiTTD . '</td>
                    <td>' . $CatatanVerifikasi . '</td>
                    <td>' . $UpdateStatusSPJ . '</td>
                </tr>';
            echo '</table>';
            echo '</div>';
            $stmt->close();

        // Check if the data is already there
        $queryBen = "SELECT CatatanBendahara, TanggalBayarBendahara, PICSelesaiTransfer FROM bendahara Where TransaksiID = ?";
        $stmtBen = $con->prepare($queryBen);
        $stmtBen->bind_param("i", $selectedTransactionID);
        $stmtBen->execute();
        $stmtBen->store_result();
        $stmtBen->bind_result($CatatanBendahara, $TanggalBayarBendahara, $PICSelesaiTransfer);
        $stmtBen->fetch();
        $stmtBen->close();

        // Display success message
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo '<h1>Data inserted successfully!</h1>';
        } else if (isset($_GET['success']) && $_GET['success'] == 2) {
            echo '<h1>Data updated successfully!</h1>';
        }

        // Bendahara FORM
        echo '<div class="container">';
        echo '<h2>Form Bendahara</h2>';
        echo '<form method="POST" action="" enctype="multipart/form-data">
        <table>
            <tr>
                <td><label for="CatatanBendahara">Catatan Bendahara:</label></td>
                <td><input type="text" name="CatatanBendahara" value="' . $CatatanBendahara . '"></td>
            </tr>

            <tr>
                <td><label for="TanggalBayarBendahara">Tanggal Bayar Bendahara:</label></td>
                <td><input type="datetime-local" name="TanggalBayarBendahara" value="' . $TanggalBayarBendahara . '" required></td>
            </tr>

            <tr>
                <td><label for="PICSelesaiTransfer">PIC Selesai Transfer:</label></td>
                <td>
                    <input type="file" name="PICSelesaiTransfer" accept="image/*" required>
                    <br>
                    <img src="data:image/jpeg;base64,' . base64_encode($PICSelesaiTransfer) . '" alt="Uploaded Image" style="max-width: 200px; max-height: 200px;">
                </td>
            </tr>
        </table>
        <div class="Signature">
            <h2>Signature by '. $CurrentAdminName .'</h2>
        </div>
        <button type="update">Update</button>
        </form>';
        echo '</div>';
        } else{
            echo '<p>Error: Transaction not found</p>';
        }
        // Display the back button
        echo '<div class="Back-Home">
                <p>Back to <a href="Bendahara.php" class="Back-button">List</a></p>
            </div>';
    } else {
        // Display the search form
        echo '<div class="container">';
        echo '<h1>Kolom Pencarian</h1>';
        echo '<form method="" action="" onsubmit="window.location.href = \'Bendahara.php?TransactionID=\' + encodeURIComponent(document.getElementById(\'TransaksiID\').value); return false;">
            <label for="TransaksiID">Search by Transaksi ID:</label>
            <input type="text" name="TransaksiID" id="TransaksiID">
            <button type="submit">Search</button>
        </form>';
        echo '<p>*Only Transaction with SPM Number can be searched</p>';
        echo '</div>';
        // Display all transaction data
        echo '<div class="Transaction-Data">';
        // Define the status of the transaction
        $sts = "SPM sudah diterbitkan";
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
        echo '<div class="container">';
        echo '<table id="transactionTable" border="1">
                <h1>Daftar Surat Perintah Membayar</h1>
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
                    <th>Action</th>
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
                    <td><a class="proceed-button" href="Bendahara.php?TransactionID=' . $TransaksiID . '">Pilih</a></td>
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
    </div>
</body>
</html>