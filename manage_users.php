<?php
session_start();
require_once 'connect.php';

// Restrict access to admin only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($name && $email && $phone && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'student', 'active')");
            $stmt->execute([$name, $email, $phone, $hashed]);
            $success = "User added successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch all student users
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, u.phone, u.status, r.room_number
    FROM users u
    LEFT JOIN rooms r ON u.room_id = r.id
    WHERE u.role = 'student'
");
$stmt->execute();
$students = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body { font-family: Arial; margin: 0; }
        .topbar { margin-left: 220px; padding: 15px; background: #ecf0f1; border-bottom: 1px solid #ccc; }
        .content { margin-left: 220px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        form input { padding: 8px; margin: 5px; width: 200px; }
        form input[type="submit"] { background: #3498db; color: white; border: none; cursor: pointer; }
        .message { margin-top: 10px; color: green; }
        .error { margin-top: 10px; color: red; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="topbar">
    Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong>
</div>

<div class="content">
    <h2>Add New Student</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="text" name="phone" placeholder="Phone" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="submit" value="Add User" />
    </form>

    <?php if ($success): ?>
        <div class="message"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

<h2>All Students</h2>
<table>
    <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Room Number</th><th>Status</th>
    </tr>
    <?php foreach ($students as $student): ?>
    <tr>
        <td><?php echo $student['id']; ?></td>
        <td><?php echo htmlspecialchars($student['name']); ?></td>
        <td><?php echo htmlspecialchars($student['email']); ?></td>
        <td><?php echo htmlspecialchars($student['phone']); ?></td>
        <td><?php echo $student['room_number'] ?? '<em>Not Assigned</em>'; ?></td>
        <td><?php echo $student['status']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</div>

</body>
</html>
