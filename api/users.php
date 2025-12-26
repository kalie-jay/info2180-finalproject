<?php
session_start();
require '../db.php';

// Only Admin can access
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo "<h2>Access Denied</h2><p>Only Admins can view or add users.</p>";
        exit;
    }
}

// Handle GET Requests (Views)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $context = $_GET['context'] ?? 'list';

    if ($context === 'create_form') {
        // Output the HTML Form for New User [cite: 36, 37]
        echo '
        <h2>New User</h2>
        <form id="new-user-form" action="api/users.php" method="POST" class="form-container">
            <div class="form-grid">
                <div class="form-group"><label>First Name</label><input type="text" name="firstname" required></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lastname" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Min 8 chars, 1 num, 1 upper, 1 letter" required></div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option value="Member">Member</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary">Save User</button>
        </form>';
        
    } else {
        // List Users [cite: 62]
        $stmt = $pdo->query("SELECT * FROM Users");
        $users = $stmt->fetchAll();
        
        echo '<div class="header-flex"><h2>Users</h2><button id="add-user-btn" class="btn-primary">+ Add User</button></div>';
        echo '<table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr></thead><tbody>';
        foreach ($users as $u) {
            $name = htmlspecialchars($u['firstname'] . ' ' . $u['lastname']);
            echo "<tr><td>$name</td><td>{$u['email']}</td><td>{$u['role']}</td><td>{$u['created_at']}</td></tr>";
        }
        echo '</tbody></table>';
    }
    exit;
}

// Handle POST Requests (Create User)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_SPECIAL_CHARS);
    $lname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pwd = $_POST['password'];
    $role = $_POST['role'];

    // Regex Validation [cite: 37]
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $pwd)) {
        echo json_encode(['success' => false, 'message' => 'Password must have 1 upper, 1 lower, 1 number, and be 8+ chars.']);
        exit;
    }

    $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT); // [cite: 38]

    try {
        $stmt = $pdo->prepare("INSERT INTO Users (firstname, lastname, password, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fname, $lname, $hashed_pwd, $email, $role]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Email likely exists.']);
    }
}
?>