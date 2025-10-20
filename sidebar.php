<!-- sidebar.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #550753 0%, #3a0444 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(0,0,0,0.2);
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .sidebar-header h2,
        .sidebar.collapsed .menu-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu li a {
            justify-content: center;
            padding: 12px;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header h2 {
            font-size: 1.4em;
            font-weight: 700;
            color: white;
            white-space: nowrap;
        }

        .sidebar-header i {
            font-size: 1.5em;
            color: #ffd700;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 8px 15px;
        }

        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar-menu li a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #ffd700;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar-menu li a:hover::before,
        .sidebar-menu li a.active::before {
            transform: scaleY(1);
        }

        .sidebar-menu li a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.15);
            font-weight: 600;
        }

        .sidebar-menu li a i {
            font-size: 1.1em;
            width: 20px;
            text-align: center;
        }

        .menu-text {
            white-space: nowrap;
        }

        .toggle-btn {
            position: absolute;
            top: 20px;
            right: -15px;
            background: #550753;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .toggle-btn:hover {
            background: #6c1480;
            transform: scale(1.1);
        }

        /* Topbar Styles */
        .topbar {
            margin-left: 250px;
            background: white;
            padding: 0 25px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: margin-left 0.3s ease;
            z-index: 999;
            position: sticky;
            top: 0;
        }

        .sidebar.collapsed ~ .topbar {
            margin-left: 70px;
        }

        .topbar-left h1 {
            color: #333;
            font-size: 1.5em;
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-info:hover {
            background: #e9ecef;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #550753, #8e44ad);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .user-role {
            color: #666;
            font-size: 12px;
        }

        .notifications {
            position: relative;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .notifications:hover {
            background: #f8f9fa;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #e74c3c;
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
        }

        /* Content Area */
        .content {
            margin-left: 250px;
            padding: 25px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 70px);
        }

        .sidebar.collapsed ~ .content {
            margin-left: 70px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .topbar, .content {
                margin-left: 0 !important;
            }

            .mobile-menu-btn {
                display: block;
                background: none;
                border: none;
                font-size: 1.2em;
                cursor: pointer;
                color: #333;
            }
        }

        /* Page specific active state */
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        .sidebar-menu li a[href="<?php echo $current_page; ?>"] {
            background: rgba(255,255,255,0.15);
            font-weight: 600;
        }

        .sidebar-menu li a[href="<?php echo $current_page; ?>"]::before {
            transform: scaleY(1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="fas fa-chevron-left" id="toggleIcon"></i>
        </button>
        
        <div class="sidebar-header">
            <i class="fas fa-crown"></i>
            <h2>Admin Panel</h2>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="admindashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admindashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a></li>
            
            <li><a href="manage_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span class="menu-text">Manage Users</span>
            </a></li>
            
            <li><a href="pending_bills.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pending_bills.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span class="menu-text">Pending Bills</span>
               
            </a></li>
            
            <li><a href="manage_rooms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span class="menu-text">Room Management</span>
            </a></li>
            
          
            
          
            
            <li style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <a href="logout.php" style="color: #ff6b6b;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()" style="display: none;">
                <i class="fas fa-bars"></i>
            </button>
            <h1><?php echo getPageTitle(basename($_SERVER['PHP_SELF'])); ?></h1>
        </div>
        
     
            
            <div class="user-info" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    <?php 
                    $username = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Admin';
                    echo strtoupper(substr($username, 0, 1)); 
                    ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo $username; ?></span>
                    <span class="user-role">Administrator</span>
                </div>
                <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content" id="mainContent">
        <!-- Page content will be inserted here -->

    <script>
        // Sidebar functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleIcon = document.getElementById('toggleIcon');
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            } else {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }
        }

        // Mobile menu functionality
        function toggleMobileMenu() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }

        // User menu functionality
        function toggleUserMenu() {
            // Add user dropdown menu functionality here
            alert('User menu clicked!');
        }

        // Auto-hide sidebar on mobile
        function checkScreenSize() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            if (window.innerWidth <= 768) {
                mobileMenuBtn.style.display = 'block';
                document.getElementById('sidebar').classList.remove('collapsed');
            } else {
                mobileMenuBtn.style.display = 'none';
                document.getElementById('sidebar').classList.remove('mobile-open');
            }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !event.target.closest('.mobile-menu-btn')) {
                sidebar.classList.remove('mobile-open');
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkScreenSize();
            window.addEventListener('resize', checkScreenSize);
        });
    </script>
</body>
</html>

<?php
// Function to get page title based on current file
function getPageTitle($filename) {
    $titles = [
        'admindashboard.php' => 'Dashboard Overview',
        'manage_users.php' => 'User Management',
        'pending_bills.php' => 'Pending Bills',
        'manage_rooms.php' => 'Room Management',
        
    ];
    
    return $titles[$filename] ?? 'Admin Panel';
}
?>