<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Focus Timer - Kelola Waktu Fokus Anda</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile.css">
    <link rel="icon" href="assets/img/logo.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/mobile.js"></script>
</head>

<body>
    <div class="container">
        <nav class="navbar">
            <ul>
                <li><a href="home.php" <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'class="active"' : ''; ?>>Timer</a></li>
                <li><a href="history.php" <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'class="active"' : ''; ?>>Riwayat</a></li>
                <li><a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
            </ul>
        </nav>