<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <style>
        body {
            background-color: #808080;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-top: 30px;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #cccccc;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .menu-container {
            margin: 20px auto;
            border-radius: 20px;
            width: 800px; /* Set a fixed width for the menu container */
        }

        .menu {
            display: flex;
            justify-content: space-around;
            background-color: #;
            border-radius: 5px;
            overflow: hidden;
        }

        .menu a {
            display: block;
            color: Black;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .menu a:hover {
            background-color: #808080;
        }

        .menu a.active {
            background-color: #808080;
        }

        .welcome-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php
    require_once "connection-Admin.php";
    require_once "authenticate-Admin.php";
    ?>
    <h1>Admin Page</h1>
    <div class="container welcome-container">
        <h2>Welcome, <?php echo $_SESSION["AdminName"]; ?></h2>
        <p>Anda telah login sebagai <?php echo $_SESSION['AdminLevel']?>. Silahkan pilih menu yang tersedia.</p>
    </div>
    <div class="menu-container">
        <div class="menu">
            <div class="container">
                <a href="Mapping-Anggaran.php">Mapping Anggaran</a>
            </div>
        </div>
        <div class="menu">
            <div class="container">
                <a href="Verifikatur.php">Verifikatur</a>
            </div>
        </div>
        <div class="menu">
            <div class="container">
                <a href="SPM.php">SPM</a>
            </div>
        </div>
        <div class="menu">
            <div class="container">
                <a href="Bendahara.php">Bendahara</a>
            </div>
        </div>
        <div class="menu">
            <div class="container">
                <a href="Admin-Register.php">Daftar Admin</a>
            </div>
            <div class="container">
                <a href="../Logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>