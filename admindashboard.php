<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>




<?php include 'sidebar.php'; ?>



<div class="content">
    <h1>Welcome, Admin!</h1>
    <p>Use the sidebar to manage users and view pending bills.</p>
</div>

</body>
</html>
