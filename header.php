<!-- header.php -->
<div class="header">
    <div class="nav-left">
        <h2>Student Portal</h2>
    </div>
    <div class="nav-right">
        Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong>
    </div>
</div>

<div class="sidebar">
    <h3>Navigation</h3>
    <ul>
        <li><a href="userdashboard.php">🙍🏻‍♂️ Dashboard</a></li>
        <li><a href="rooms.php">🏠 Rooms</a></li>
        <li><a href="payments.php">💳 Payments</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</div>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
}
.header {
    background-color: #550753ff;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-left: 200px; 
}
.sidebar {
    width: 200px;
    background-color: #51105bff;
    color: white;
    height: 100vh;
    padding: 5px;
    position: fixed;
    top: 0;
    left: 0;
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
    padding: 20px;
}
</style>
