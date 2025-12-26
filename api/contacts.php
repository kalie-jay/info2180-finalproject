<?php
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

// --- GET REQUESTS (VIEWS) ---
if ($method === 'GET') {
    // 1. Show Create Form
    if (isset($_GET['context']) && $_GET['context'] === 'create_form') {
        // Fetch users for dropdown [cite: 149]
        $users = $pdo->query("SELECT id, firstname, lastname FROM Users")->fetchAll();
        $userOptions = "";
        foreach ($users as $u) {
            $userOptions .= "<option value='{$u['id']}'>{$u['firstname']} {$u['lastname']}</option>";
        }

        echo "
        <h2>New Contact</h2>
        <form id='new-contact-form' action='api/contacts.php' method='POST'>
            <div class='form-grid'>
                <div class='form-group'><label>Title</label><select name='title'><option>Mr</option><option>Ms</option><option>Mrs</option></select></div>
                <div class='form-group'><label>First Name</label><input type='text' name='firstname' required></div>
                <div class='form-group'><label>Last Name</label><input type='text' name='lastname' required></div>
                <div class='form-group'><label>Email</label><input type='email' name='email' required></div>
                <div class='form-group'><label>Telephone</label><input type='text' name='telephone'></div>
                <div class='form-group'><label>Company</label><input type='text' name='company'></div>
                <div class='form-group'><label>Type</label><select name='type'><option value='Sales Lead'>Sales Lead</option><option value='Support'>Support</option></select></div>
                <div class='form-group'><label>Assigned To</label><select name='assigned_to'>$userOptions</select></div>
            </div>
            <button type='submit' class='btn-primary'>Save Contact</button>
        </form>";
        exit;
    }

    // 2. View Single Contact Details [cite: 177, 178]
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT c.*, u.firstname as assign_fn, u.lastname as assign_ln, 
                               cr.firstname as create_fn, cr.lastname as create_ln
                               FROM Contacts c 
                               LEFT JOIN Users u ON c.assigned_to = u.id 
                               LEFT JOIN Users cr ON c.created_by = cr.id
                               WHERE c.id = ?");
        $stmt->execute([$id]);
        $c = $stmt->fetch();

        if (!$c) { echo "Contact not found"; exit; }

        $badgeClass = ($c['type'] == 'Sales Lead') ? 'sales' : 'support';

        echo "
        <div class='header-flex'>
            <div style='display:flex;align-items:center;gap:10px;'>
                <img src='user-icon.png' style='width:50px;height:50px;border-radius:50%;background:#ddd;'> <div>
                    <h2>{$c['title']} {$c['firstname']} {$c['lastname']}</h2>
                    <p class='text-muted'>Created on {$c['created_at']} by {$c['create_fn']} {$c['create_ln']}</p>
                    <p class='text-muted'>Updated on {$c['updated_at']}</p>
                </div>
            </div>
            <div>
                <button id='assign-me-btn' class='btn-primary'>Assign to me</button>
                <button id='switch-type-btn' class='btn-primary'>Switch to " . ($c['type'] == 'Support' ? 'Sales Lead' : 'Support') . "</button>
            </div>
        </div>
        
        <div class='form-grid' style='margin-top:20px;'>
            <div><label>Email</label><p>{$c['email']}</p></div>
            <div><label>Telephone</label><p>{$c['telephone']}</p></div>
            <div><label>Company</label><p>{$c['company']}</p></div>
            <div><label>Assigned To</label><p>{$c['assign_fn']} {$c['assign_ln']}</p></div>
        </div>

        <div style='margin-top:30px;'>
            <h3>Notes</h3>
            <div id='notes-area'></div>
            <h4>Add a note about {$c['firstname']}</h4>
            <textarea id='note-comment' rows='4' style='width:100%'></textarea>
            <button id='add-note-btn' class='btn-primary' style='margin-top:10px'>Add Note</button>
        </div>";
        exit;
    }

    // 3. Dashboard List (Default) [cite: 104]
    $filter = $_GET['filter'] ?? 'All';
    $sql = "SELECT * FROM Contacts";
    $params = [];

    if ($filter == 'Sales Leads') {
        $sql .= " WHERE type = 'Sales Lead'";
    } elseif ($filter == 'Support') {
        $sql .= " WHERE type = 'Support'";
    } elseif ($filter == 'Assigned to me') {
        $sql .= " WHERE assigned_to = ?";
        $params[] = $_SESSION['user_id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll();

    echo "
    <div class='header-flex'>
        <h2>Dashboard</h2>
        <button id='dashboard-add-btn' class='btn-primary'>+ Add Contact</button>
    </div>
    <div style='margin: 15px 0;'>
        <strong>Filter By: </strong>
        <a href='#' class='filter-btn' data-filter='All'>All</a> |
        <a href='#' class='filter-btn' data-filter='Sales Leads'>Sales Leads</a> |
        <a href='#' class='filter-btn' data-filter='Support'>Support</a> |
        <a href='#' class='filter-btn' data-filter='Assigned to me'>Assigned to me</a>
    </div>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Type</th><th></th></tr></thead>
        <tbody>";
    
    foreach ($contacts as $c) {
        $badge = ($c['type'] == 'Support') ? 'support' : 'sales';
        echo "<tr>
            <td>{$c['title']} {$c['firstname']} {$c['lastname']}</td>
            <td>{$c['email']}</td>
            <td>{$c['company']}</td>
            <td><span class='badge $badge'>{$c['type']}</span></td>
            <td><a href='#' class='view-contact-btn' data-id='{$c['id']}'>View</a></td>
        </tr>";
    }
    echo "</tbody></table>";
    exit;
}

// --- POST REQUESTS (ACTIONS) ---
if ($method === 'POST') {
    // Check if it's a JSON request (Assign/Switch)
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input) {
        // Update Action [cite: 181]
        $contact_id = $input['contact_id'];
        if ($input['action'] == 'assign_to_me') {
            $sql = "UPDATE Contacts SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
            $pdo->prepare($sql)->execute([$_SESSION['user_id'], $contact_id]);
        } elseif ($input['action'] == 'switch_type') {
            // Toggle Logic
            $curr = $pdo->query("SELECT type FROM Contacts WHERE id = $contact_id")->fetchColumn();
            $newType = ($curr == 'Sales Lead') ? 'Support' : 'Sales Lead';
            $sql = "UPDATE Contacts SET type = ?, updated_at = NOW() WHERE id = ?";
            $pdo->prepare($sql)->execute([$newType, $contact_id]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // Form Submission (New Contact) [cite: 151, 152]
    $title = $_POST['title'];
    $fname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_SPECIAL_CHARS);
    $lname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $tel = htmlspecialchars($_POST['telephone']);
    $comp = htmlspecialchars($_POST['company']);
    $type = $_POST['type'];
    $assign = $_POST['assigned_to'];
    $created_by = $_SESSION['user_id'];

    $sql = "INSERT INTO Contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    if ($pdo->prepare($sql)->execute([$title, $fname, $lname, $email, $tel, $comp, $type, $assign, $created_by])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB Error']);
    }
    exit;
}
?>