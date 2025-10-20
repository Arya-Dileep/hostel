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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 25px;
        }
        h1 {
            color: #1e293b;
            font-size: 28px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        h1::before {
            content: "👥";
            font-size: 24px;
        }
        h2 {
            color: #374151;
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }
        .btn-primary::before {
            content: "➕";
        }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 500;
        }
        .success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }
        tr:hover {
            background-color: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .room-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .room-assigned {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .room-unassigned {
            background-color: #f3f4f6;
            color: #6b7280;
            font-style: italic;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            margin-right: 10px;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .card {
                padding: 20px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            th, td {
                padding: 12px 8px;
                font-size: 14px;
            }
            .user-info {
                flex-direction: column;
                align-items: flex-start;
            }
            .user-avatar {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>


<?php include 'sidebar.php'; ?>

<div class="container">
    <div class="card">
        <h1>User Management</h1>
        
        <h2>Add New Student</h2>
        <form method="POST" id="userForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" placeholder="Enter full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Student</button>
        </form>

        <?php if ($success): ?>
            <div class="message success">✅ <?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="message error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>All Students</h2>
        
        <?php if (count($students) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Contact Info</th>
                            <th>Room Assigned</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                        <div style="font-size: 12px; color: #6b7280;">ID: <?php echo $student['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="margin-bottom: 5px;">📧 <?php echo htmlspecialchars($student['email']); ?></div>
                                <div style="font-size: 14px; color: #6b7280;">📞 <?php echo htmlspecialchars($student['phone']); ?></div>
                            </td>
                            <td>
                                <?php if ($student['room_number']): ?>
                                    <span class="room-badge room-assigned">Room <?php echo $student['room_number']; ?></span>
                                <?php else: ?>
                                    <span class="room-badge room-unassigned">Not Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $student['status']; ?>">
                                    <?php echo ucfirst($student['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; color: #94a3b8;">
                <div style="font-size: 48px; margin-bottom: 15px;">👥</div>
                <h3>No Students Found</h3>
                <p>No student accounts have been created yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Form validation and enhancement
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('userForm');
        const passwordInput = document.getElementById('password');
        
        // Add real-time password strength indicator
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('password-strength') || createStrengthIndicator();
            
            let strength = 'Weak';
            let color = '#ef4444';
            
            if (password.length >= 8) {
                strength = 'Medium';
                color = '#f59e0b';
            }
            if (password.length >= 12 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                strength = 'Strong';
                color = '#10b981';
            }
            
            strengthIndicator.textContent = `Strength: ${strength}`;
            strengthIndicator.style.color = color;
        });
        
        function createStrengthIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'password-strength';
            indicator.style.fontSize = '12px';
            indicator.style.marginTop = '5px';
            indicator.style.fontWeight = '600';
            passwordInput.parentNode.appendChild(indicator);
            return indicator;
        }
        
        // Add animation to new rows
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>

</body>
</html>