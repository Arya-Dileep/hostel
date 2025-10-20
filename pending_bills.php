<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Fetch all active students with room info
$stmt = $pdo->prepare("
    SELECT u.id AS user_id, u.name, u.email, r.room_number, r.price
    FROM users u
    LEFT JOIN rooms r ON u.room_id = r.id
    WHERE u.role = 'student' AND u.status = 'active'
");
$stmt->execute();
$students = $stmt->fetchAll();

// Build payment status list
$results = [];
$totalPending = 0;
$totalPaid = 0;

foreach ($students as $student) {
    // Check if payment exists for this user in current month
    $paymentStmt = $pdo->prepare("
        SELECT p.id
        FROM payments p
        JOIN bills b ON p.bill_id = b.id
        WHERE b.user_id = ? AND MONTH(p.paid_at) = ? AND YEAR(p.paid_at) = ?
    ");
    $paymentStmt->execute([$student['user_id'], $currentMonth, $currentYear]);
    $paid = $paymentStmt->fetchColumn();

    $status = $paid ? 'Paid' : 'Pending';
    $amount = $student['price'] ?? 0;
    
    if ($status === 'Paid') {
        $totalPaid += $amount;
    } else {
        $totalPending += $amount;
    }

    $results[] = [
        'name' => $student['name'],
        'email' => $student['email'],
        'room' => $student['room_number'] ?? 'Not Assigned',
        'amount' => $amount,
        'status' => $status
    ];
}

$totalStudents = count($results);
$paidCount = count(array_filter($results, fn($row) => $row['status'] === 'Paid'));
$pendingCount = $totalStudents - $paidCount;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Bills</title>
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
            content: "📊";
            font-size: 24px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-card.pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card.paid {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-card.total {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
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
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .amount {
            font-weight: 600;
            color: #1e293b;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
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
        }
        .user-details {
            display: flex;
            flex-direction: column;
        }
        .user-name {
            font-weight: 600;
            color: #1e293b;
        }
        .user-email {
            font-size: 12px;
            color: #64748b;
        }
        .room-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background-color: #e0e7ff;
            color: #3730a3;
        }
        .no-room {
            color: #94a3b8;
            font-style: italic;
        }
        .month-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        .month-title {
            font-size: 20px;
            font-weight: 600;
            color: #374151;
        }
        .export-btn {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .export-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .card {
                padding: 20px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .month-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            th, td {
                padding: 12px 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container">
    <div class="card">
        <h1>Billing Overview</h1>
        
        <div class="month-header">
            <div class="month-title"><?php echo date('F Y'); ?> - Payment Status</div>
            <a href="download_bills.php" class="export-btn">
                📥 Export Report
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-value"><?php echo $totalStudents; ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card paid">
                <div class="stat-value"><?php echo $paidCount; ?></div>
                <div class="stat-label">Payments Received</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-value"><?php echo $pendingCount; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₹<?php echo number_format($totalPending); ?></div>
                <div class="stat-label">Total Pending Amount</div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Room</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                </div>
                                <div class="user-details">
                                    <div class="user-name"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($row['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($row['room'] !== 'Not Assigned'): ?>
                                <span class="room-badge">Room <?php echo htmlspecialchars($row['room']); ?></span>
                            <?php else: ?>
                                <span class="no-room">Not Assigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="amount">₹<?php echo htmlspecialchars($row['amount']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Add subtle animations to table rows
    document.addEventListener('DOMContentLoaded', function() {
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

        // Add click effect to stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
    });
</script>

</body>
</html>