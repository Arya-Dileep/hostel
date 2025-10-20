<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        .sidebar {
            width: 200px;
            background-color: #51105bff;
            color: white;
            height: 100vh;
            padding: 5px;
            position: fixed;
        }
        .sidebar h3 {
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }
        .sidebar ul li a:hover {
            text-decoration: underline;
        }
        .main {
            margin-left: 200px;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>



<div class="main">
    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
        <p>Use the sidebar to view your room details and payment history.</p>
    </div>
</div>

</body>
</html>
