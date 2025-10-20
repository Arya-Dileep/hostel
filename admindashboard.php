<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .topbar {
            margin-left: 220px; /* match sidebar width */
            background-color: #ecf0f1;
            padding: 15px;
            border-bottom: 1px solid #ccc;
        }

        .content {
            margin-left: 220px; /* match sidebar width */
            padding: 20px;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="topbar">
    Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong>
</div>

<div class="content">
    <h1>Welcome, Admin!</h1>
    <p>Use the sidebar to manage users and view pending bills.</p>
</div>

</body>
</html>
