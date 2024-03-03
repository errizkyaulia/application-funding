<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <?php
    require_once '../connection.php';
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
    <div class="container">
    <h1>Transaction History <?php echo $fullName; ?></h1>

    <form method="POST" action="">
        <label for="TransaksiID">Transaction ID:</label>
        <input type="text" name="TransaksiID" id="TransaksiID">
        <button type="submit">Search</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $TransaksiID = $_POST['TransaksiID'];
        
        // Validate the transaction ID
        $queryID = "SELECT TransaksiID FROM transaksi WHERE TransaksiID = ? AND UserID = ?";
        $stmtID = $con->prepare($queryID);
        $stmtID->bind_param("ii", $TransaksiID, $userId);
        $stmtID->execute();
        $stmtID->bind_result($ResultTransaksiID);
        $stmtID->fetch();
        $stmtID->close();
        
        // Display error message if the transaction ID is not found
        if (empty($ResultTransaksiID)) {
            $error_message = "Invalid Transaction ID";
        } else {
            // Perform any necessary processing with the transaction ID
            $query = "SELECT p.PembebananID, t.TanggalPengajuan, t.NomorStJenisKeg, t.catatan, t.status
            FROM transaksi t
            JOIN pembebanan p ON t.TransaksiID = p.TransaksiID
            WHERE t.TransaksiID = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("i", $ResultTransaksiID);
            $stmt->execute();
            $stmt->bind_result($PembebananID, $TanggalPengajuan, $NomorStJenisKeg, $catatan, $status);

            // Fetch and display the result
            $stmt->fetch();
            // Check if the transaction status is empty
            if (empty($PembebananID)) {
                echo "<p>Data Transaksi sedang di Proses.</p>";
                echo "<p>Mohon untuk menunggu beberapa saat kembali.</p>";
                echo "<p>Silahkan klik <a href='Tracking.php'>Lacak Pengajuan</a> untuk melacak pengajuan Anda</p>";
            } else {
                // Display the transaction details in a table
                echo "<p>This is what you looking for</p>";
                echo '<table border="1">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Pembebanan ID</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Nomor ST / Jenis Kegunaan</th>
                            <th>Catatan</th>
                            <th>Status</th>
                        </tr>';
                echo '<tr>
                        <td>' . $TransaksiID . '</td>
                        <td>' . $PembebananID . '</td>
                        <td>' . $TanggalPengajuan . '</td>
                        <td>' . $NomorStJenisKeg . '</td>
                        <td>' . $catatan . '</td>
                        <td>' . $status . '</td>
                    </tr>';
                echo '</table>';
                $stmt->close();
            }
        }

        // Display error message if set
        if (!empty($error_message)) {
            echo "<div class='message error'>
                    <p>$error_message</p>
                </div><br>";
        }
    }
    ?>
    </div>
    <div class="container">
    <div class="Transaction-Data">
        <h2>Here is All of Yours Transaction Data</h2>
        <?php
        // Fetch transaction data from the database
        $query = "SELECT TransaksiID, TanggalPengajuan, NomorStJenisKeg, catatan, status
        FROM transaksi
        WHERE UserID = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($TransaksiID, $TanggalPengajuan, $NomorStJenisKeg, $catatan, $status);

        // Display the transaction data in a table
        echo '<table border="1">
                <tr>
                    <th>No</th>
                    <th>Transaction ID</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Nomor ST / Jenis Kegunaan</th>
                    <th>Catatan</th>
                    <th>Status</th>
                </tr>';

        // Fetch and display each row of data
        $count = 1;
        while ($stmt->fetch()) {
            echo '<tr>
                    <td>' . $count . '</td>
                    <td>' . $TransaksiID . '</td>
                    <td>' . $TanggalPengajuan . '</td>
                    <td>' . $NomorStJenisKeg . '</td>
                    <td>' . $catatan . '</td>
                    <td>' . $status . '</td>
                  </tr>';
            $count++;
        }

        echo '</table>';
        $stmt->close();
        ?>
    </div>
    </div>

    <div class="Back-Home">
        <p>Back to <a href="Home.php" class="Back-button">Home</a></p>
    </div>
</body>
</html>