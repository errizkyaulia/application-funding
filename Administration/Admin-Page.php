<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="Admin-Page-Style.css" rel="stylesheet" type="text/css">
    <title>Admin Page</title>
</head>
<body>
    <?php
    require "connection-Admin.php";
    require "authenticate-Admin.php";
    ?>
    <div class="welcome-container">
        <h1>Admin Page</h1>
        <h2>Welcome, <?php echo $_SESSION["AdminName"]; ?></h2>
        <p>Anda telah login sebagai <?php echo $_SESSION['AdminLevel']?>. Silahkan pilih menu yang tersedia.</p>
    </div>
    <nav>
      <div class="navbar">
        <div class="container nav-container">
            <input class="checkbox" type="checkbox" name="" id="" />
            <div class="checkbox-lines">
              <span class="line line1"></span>
              <span class="line line2"></span>
              <span class="line line3"></span>
            </div>  
          <div class="logo">
            <h1>MENU</h1>
          </div>
          <div class="menu-items">
            <li><a href="#">Home</a></li>
            <li><a href="Mapping-Anggaran.php">Mapping Anggaran</a></li>
            <li><a href="Verifikatur.php">Verifikatur</a></li>
            <li><a href="SPM.php">SPM</a></li>
            <li><a href="Bendahara.php">Bendahara</a></li>
            <li><a href="Admin-Register.php">Daftar Admin</a></li>
            <li><a href="../Logout.php">Logout</a></li>
          </div>
        </div>
      </div>
    </nav>
</body>
</html>