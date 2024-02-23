<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" main="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pengajuan</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap">
    <link href="styleUser.css" rel="stylesheet" type="text/css">
    <?php 
    require 'connection-User.php';
    require 'authenticate.php';

    // Get user ID from the username
    $userid = $_SESSION['user'];
    // Fetch data from the database
    $userId = $_SESSION['user'];
    $query = "SELECT fullName, email, phoneNumber, bidang, PIC FROM userdata WHERE UserID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($fullName, $email, $phoneNumber, $bidang, $PIC);
    $stmt->fetch();
    $stmt->close();

    // Display the user's profile picture
    if (!empty($PIC)) {
        $base64Image = base64_encode($PIC);
        $imageSrc = "data:image/jpeg;base64," . $base64Image;
    } else {
        // Default image path or URL
        $defaultImageSrc = "image/profile.png";
        $imageSrc = $defaultImageSrc;
    }
    ?>
    <header>
        <h1>Aplikasi Pengajuan</h1>
    </header>
</head>
<body>
    <!-- Navigation menu main goes here -->
    <nav>
        <ul class="nav-list">
            <li class="active">
                <a href="Home.php" aria-current="page">Home</a>
            </li>
            <li>
                <a href="Pengajuan.php">Pengajuan</a>
            </li>
            <li>
                <a href="Tracking.php">Lacak Pengajuan</a>
            </li>
            <li>
                <a href="History.php">History</a>
            </li>
            <li>
                <a href="Profile.php">Profile</a>
            </li>
            <li>
                <a href="../Logout.php">Logout</a>
            </li>
        </ul>
    </nav>
    <div class="main">
        <img src="<?php echo $imageSrc; ?>" alt="Profile Picture">
        <h2>Welcome <?php echo $fullName; ?></h2>
        <h3>Status Pengajuan Terakhir Anda:</h3>
        <?php
            // Fetch data from the database
            $query = "SELECT TransaksiID, TanggalPengajuan, NomorStJenisKeg, catatan, status 
            FROM transaksi
            WHERE UserID = ?
            ORDER BY TanggalPengajuan DESC
            LIMIT 1";
            $stmt = $con->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($TransaksiID, $TanggalPengajuan, $NomorStJenisKeg, $catatan, $status);
            $stmt->fetch();
            // Display the latest transaction data
            if (!empty($status)) {
                echo "Status dari pengajuan terkahir anda dengan ID: " . $TransaksiID;
                echo '<br>';
                echo "Status saat ini: " . $status;

                echo '<table>
                        <tr>
                            <th>Nomor</th>
                            <th>Transaction ID</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Nomor ST / Jenis Kegunaan</th>
                            <th>Catatan</th>
                            <th>Status</th>
                        </tr>';

                echo '<tr>
                        <td>1</td>
                        <td>' . $TransaksiID . '</td>
                        <td>' . $TanggalPengajuan . '</td>
                        <td>' . $NomorStJenisKeg . '</td>
                        <td>' . $catatan . '</td>
                        <td>' . $status . '</td>
                    </tr>';

                echo '</table>';
                $stmt->close();
            } else {
                echo "Anda belum pernah mengajukan pengajuan.";
            }
        ?>
        <h3>Pengajuan Anda yang Sedang Diproses:</h3>
        <?php
            $sts1 = "Dalam Proses";
            $sts2 = "Sudah Di Anggarkan";
            $sts3 = "Sudah di Verifikasi";
            // Fetch data from the database
            $queryDiProses = "SELECT t.TransaksiID, p.PembebananID, t.TanggalPengajuan, t.NomorStJenisKeg, t.catatan, t.status 
            FROM transaksi t
            JOIN pembebanan p ON t.TransaksiID = p.TransaksiID
            WHERE UserID = ? AND (status = ? OR status = ? OR status = ?)";
            $stmtPros = $con->prepare($queryDiProses);
            $stmtPros->bind_param("isss", $userId, $sts1, $sts2, $sts3);
            $stmtPros->execute();
            $stmtPros->bind_result($TransaksiID, $PembebananID, $TanggalPengajuan, $NomorStJenisKeg, $catatan, $status);

            if ($stmtPros->fetch()) {
                echo "Berikut adalah daftar pengajuan Anda yang sedang diproses: ";
                echo '<table>
                    <tr>
                        <th>Nomor</th>
                        <th>Transaction ID</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Nomor ST / Jenis Kegunaan</th>
                        <th>Catatan</th>
                        <th>Status</th>
                    </tr>';

                $counter = 1;
                do {
                    echo '<tr>
                        <td>' . $counter . '</td>
                        <td>' . $TransaksiID . '</td>
                        <td>' . $TanggalPengajuan . '</td>
                        <td>' . $NomorStJenisKeg . '</td>
                        <td>' . $catatan . '</td>
                        <td>' . $status . '</td>
                    </tr>';
                    $counter++;
                } while ($stmtPros->fetch());
                echo '</table>';
                echo "Total pengajuan yang sedang diproses: " . $counter - 1 . " pengajuan.";
                $stmtPros->close();
            } else {
                echo "Tidak ada pengajuan dalam Proses";
            }
        ?>
    </div>
</body>
<footer>
        <p>&copy; <?php echo date("Y"); ?> Created by Er and enhanced by AI. All rights reserved.</p>
    </footer>
</html>