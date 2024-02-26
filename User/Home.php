<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="HomeStyle.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    const mobileScreen = window.matchMedia("(max-width: 990px )");
    $(document).ready(function () {
        $(".dashboard-nav-dropdown-toggle").click(function () {
            $(this).closest(".dashboard-nav-dropdown")
                .toggleClass("show")
                .find(".dashboard-nav-dropdown")
                .removeClass("show");
            $(this).parent()
                .siblings()
                .removeClass("show");
        });
        $(".menu-toggle").click(function () {
            if (mobileScreen.matches) {
                $(".dashboard-nav").toggleClass("mobile-show");
            } else {
                $(".dashboard").toggleClass("dashboard-compact");
            }
        });
    });
    </script>
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
</head>
<body>
<div class='dashboard'>
    <div class="dashboard-nav">
        <header>
            <a href="#!" class="menu-toggle">
                <i class="fas fa-bars"></i></a>
                <a href="#" class="brand-logo">
                    <i class="fas fa-anchor"></i>
                    <span>MENU</span></a>
        </header>
        <nav class="dashboard-nav-list">
            <a href="#" class="dashboard-nav-item">
                <i class="fas fa-home"></i>
                Home </a>
            <a href="#" class="dashboard-nav-item active">
                <i class="fas fa-tachometer-alt"></i> 
                Dashboard </a>
            <a href="Pengajuan.php" class="dashboard-nav-item">
                <i class="fas fa-file-upload"></i>
                Pengajuan </a>
                    <div class='dashboard-nav-dropdown'>
                        <a href="Tracking.php" class="dashboard-nav-item dashboard-nav-dropdown-toggle">
                            <i class="fas fa-photo-video"></i>
                            Lacak Pengajuan </a>
                                <div class='dashboard-nav-dropdown-menu'>
                                    <a href="Tracikig.php" class="dashboard-nav-dropdown-item">All</a>
                                    <a href="Tracking.php" class="dashboard-nav-dropdown-item">Proses Mapping Anggaran</a>
                                    <a href="Tracking.php" class="dashboard-nav-dropdown-item">Sudah di Validasi</a>
                                    <a href="Tracking.php" class="dashboard-nav-dropdown-item">Selesai</a>
                                </div>
                    </div>
            <div class='dashboard-nav-dropdown'>
                <a href="History.php" class="dashboard-nav-item dashboard-nav-dropdown-toggle">
                <i class="fas fa-users"></i> History </a>
                <div class='dashboard-nav-dropdown-menu'>
                    <a href="#" class="dashboard-nav-dropdown-item">All</a>
                    <a href="#" class="dashboard-nav-dropdown-item">Di Proses</a>
                    <a href="#" class="dashboard-nav-dropdown-item">Selesai</a>
                    <a href="#" class="dashboard-nav-dropdown-item">Dibatalkan</a>
                    <a href="#" class="dashboard-nav-dropdown-item">Ditolak</a>
                </div>
            </div>
            <a href="#" class="dashboard-nav-item"><i class="fas fa-cogs"></i> Settings </a>
            <a href="Profile.php" class="dashboard-nav-item"><i class="fas fa-user"></i> Profile </a>
          <div class="nav-item-divider"></div>
          <a href="../Logout.php" class="dashboard-nav-item"><i class="fas fa-sign-out-alt"></i> Logout </a>
        </nav>
    </div>

    <div class='dashboard-app'>
        <header class='dashboard-toolbar'><a href="#!" class="menu-toggle"><i class="fas fa-bars"></i></a></header>
        <div class='dashboard-content'>
            <div class='container'>
                <div class='card'>
                    <div class='card-header'>
                        <h1>Welcome back <?php echo $fullName; ?></h1>
                    </div>
                    <div class='card-body'>
                        <img src="<?php echo $imageSrc; ?>" alt="Profile Picture">
                        <p></p>
                    </div>
                </div>
            </div>
            <div class='container'>
                <div class='card'>
                    <div class='card-header'>
                        <h2>Status Pengajuan Terakhir Anda:</h2>
                    </div>
                    <div class='card-body'>
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
                            echo "ID: " . $TransaksiID;
                            echo '<br>';
                            echo "Status: " . $status;
                            $stmt->close();
                        } else {
                            echo "Anda belum pernah mengajukan pengajuan.";
                        }
                    ?>
                    </div>
                </div>
                <div class='card'>
                    <div class='card-header'>
                        <h2>Pengajuan Anda yang Sedang Diproses:</h2>
                    </div>
                    <div class='card-body'>
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
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>