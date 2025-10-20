<?php
// header.php - Enhanced User Header with Sidebar
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #550753 0%, #3a0444 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 250px;
            height: 70px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 999;
            transition: margin-left 0.3s ease;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            font-size: 1.8em;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: #ffd700;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.1);
            padding: 8px 16px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-info:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #ffd700, #ff6b6b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: white;
        }

        .user-role {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
        }

        .notifications {
            position: relative;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .notifications:hover {
            background: rgba(255,255,255,0.1);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e74c3c;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #51105b 0%, #3a0444 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(0,0,0,0.2);
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .sidebar-header h3,
        .sidebar.collapsed .menu-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu li a {
            justify-content: center;
            padding: 15px;
        }

        .sidebar.collapsed ~ .header {
            margin-left: 70px;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            font-size: 1.4em;
            font-weight: 600;
            color: white;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 5px 15px;
        }

        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
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
            font-size: 1.2em;
            width: 20px;
            text-align: center;
        }

        .menu-text {
            white-space: nowrap;
            font-weight: 500;
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

        /* Main Content Area */
        .main {
            margin-left: 250px;
            padding: 30px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 70px);
        }

        .sidebar.collapsed ~ .main {
            margin-left: 70px;
        }

        /* Mobile Responsive */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.3em;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .header, .main {
                margin-left: 0 !important;
            }

            .mobile-menu-btn {
                display: block;
            }

            .nav-right .user-details {
                display: none;
            }

            .header {
                padding: 15px 20px;
            }
        }

        /* Quick Stats in Header */
        .quick-stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
        }

        .stat-item i {
            color: #ffd700;
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
            <h3>Student Portal</h3>
            <p>Hostel Management System</p>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="userdashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'userdashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a></li>
            
            <li><a href="rooms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>">
                <i class="fas fa-bed"></i>
                <span class="menu-text">Room Management</span>
            </a></li>
            
            <li><a href="book_room.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'book_room.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span class="menu-text">My Room</span>
            </a></li>
            
            <li><a href="payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i>
                <span class="menu-text">Payments</span>
                
            </a></li>
            
          
            
            <li style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <a href="logout.php" style="color: #ff6b6b;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="nav-left">
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo">
                <i class="fas fa-building"></i>
                <span>Hostel Management</span>
            </div>
        </div>
        
        <div class="nav-right">
  
            
            <div class="user-info" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    <?php 
                    $username = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Student';
                    echo strtoupper(substr($username, 0, 1)); 
                    ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    <span class="user-role">Student</span>
                </div>
                <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main" id="mainContent">
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
            alert('User profile menu clicked!');
        }

        // Auto-hide sidebar on mobile and handle responsive behavior
        function checkScreenSize() {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768) {
                mobileMenuBtn.style.display = 'block';
                sidebar.classList.remove('collapsed');
            } else {
                mobileMenuBtn.style.display = 'none';
                sidebar.classList.remove('mobile-open');
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
            
            // Add active class based on current page
            const currentPage = '<?php echo basename($_SERVER['PHP_SELF']); ?>';
            const menuItems = document.querySelectorAll('.sidebar-menu a');
            menuItems.forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>