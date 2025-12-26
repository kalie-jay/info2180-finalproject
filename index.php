<?php
session_start();
// If not logged in, redirect to login page (we can handle this via JS or PHP, here PHP is safer)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dolphin CRM</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> </head>
<body>
    <header>
        <div class="header-content">
            <img src="dolphin.png" alt="Dolphin" class="logo-icon"> <h1>Dolphin CRM</h1>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="#" id="nav-home">Home</a></li>
                    <li><a href="#" id="nav-new-contact">New Contact</a></li>
                    <li><a href="#" id="nav-users">Users</a></li>
                    <hr>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main id="main-content">
            <h2>Dashboard</h2>
            <div id="result">Loading...</div>
        </main>
    </div>

    <script src="app.js"></script>
</body>
</html>