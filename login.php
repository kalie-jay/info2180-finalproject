<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['firstname'] = $user['firstname'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Dolphin CRM</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h1>Login</h1>
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-primary">Login</button>
        </form>
        <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <footer>Copyright © 2022 Dolphin CRM</footer>
    </div>
</body>
</html>