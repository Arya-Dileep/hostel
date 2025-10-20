<!-- sidebar.php -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="manage_users.php">👥 Users</a></li>
        <li><a href="pending_bills.php">📄 Pending Bills</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</div>

<style>
.sidebar {
    width: 220px;
    background-color: #550753ff;
    color: white;
    height: 100vh;
    padding: 5px;
    position: fixed;
    top: 0;
    left: 100;
}
.sidebar-header {
    margin-bottom: 30px;
    font-size: 20px;
    font-weight: bold;
}
.sidebar-menu {
    list-style: none;
    padding: 0;
}
.sidebar-menu li {
    margin: 20px 0;
}
.sidebar-menu li a {
    color: white;
    text-decoration: none;
    font-size: 16px;
    display: block;
    padding: 8px 12px;
    border-radius: 4px;
}
.sidebar-menu li a:hover {
    background-color: #34495e;
}
</style>
