<?php
require 'db.php';

// 1. The password we want to use
$password = 'password123';

// 2. Hash it using PHP's default algorithm
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 3. The email for the admin
$email = 'admin@project2.com';

try {
    // Check if the user already exists to decide whether to UPDATE or INSERT
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists, so we just update their password
        $update = $pdo->prepare("UPDATE Users SET password = :password WHERE email = :email");
        $update->execute(['password' => $hashed_password, 'email' => $email]);
        echo "<h1>Success!</h1><p>Admin password has been reset to: <strong>password123</strong></p>";
    } else {
        // User doesn't exist, so we create them
        $insert = $pdo->prepare("INSERT INTO Users (firstname, lastname, password, email, role, created_at) VALUES ('System', 'Admin', :password, :email, 'Admin', NOW())");
        $insert->execute(['password' => $hashed_password, 'email' => $email]);
        echo "<h1>Success!</h1><p>Admin user created with password: <strong>password123</strong></p>";
    }
    
    echo "<p><a href='login.php'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>