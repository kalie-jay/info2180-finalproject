<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // List Notes [cite: 183]
    $contact_id = $_GET['contact_id'];
    $stmt = $pdo->prepare("SELECT n.*, u.firstname, u.lastname 
                           FROM Notes n 
                           JOIN Users u ON n.created_by = u.id 
                           WHERE n.contact_id = ? ORDER BY n.created_at DESC");
    $stmt->execute([$contact_id]);
    $notes = $stmt->fetchAll();

    foreach ($notes as $n) {
        $date = date("F j, Y \a\\t g:ia", strtotime($n['created_at']));
        echo "
        <div style='border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:5px;'>
            <strong>{$n['firstname']} {$n['lastname']}</strong>
            <p>{$n['comment']}</p>
            <small style='color:#666'>$date</small>
        </div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Note [cite: 217]
    $contact_id = $_POST['contact_id'];
    $comment = htmlspecialchars($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO Notes (contact_id, comment, created_by, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$contact_id, $comment, $user_id]);

    // Update Contact timestamp
    $pdo->prepare("UPDATE Contacts SET updated_at = NOW() WHERE id = ?")->execute([$contact_id]);

    echo json_encode(['success' => true]);
}
?>