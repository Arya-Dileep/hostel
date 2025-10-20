<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT p.razorpay_payment_id, p.amount, p.paid_at, r.room_number
    FROM payments p
    JOIN bills b ON p.bill_id = b.id
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY p.paid_at DESC
");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment History</title>
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
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        h1::before {
            content: "📊";
            font-size: 24px;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 15px;
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
            content: "📥";
        }
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 150px;
            padding: 20px;
            background: #f1f5f9;
            border-radius: 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        .payment-id {
            font-family: monospace;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
        }
        .amount {
            font-weight: 600;
            color: #059669;
        }
        .no-payments {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .no-payments-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        .date {
            color: #64748b;
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            .stats {
                flex-direction: column;
            }
            table {
                display: block;
                overflow-x: auto;
            }
            th, td {
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <div class="card">
        <h1>Payment History</h1>
        
        <div class="header-actions">
            <a href="download_payments.php" class="btn btn-primary">Download PDF</a>
            
            <?php if (count($payments) > 0): ?>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($payments); ?></div>
                    <div class="stat-label">Total Payments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₹<?php 
                        $total = 0;
                        foreach ($payments as $p) {
                            $total += $p['amount'];
                        }
                        echo number_format($total, 2);
                    ?></div>
                    <div class="stat-label">Total Amount Paid</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php 
                            if (count($payments) > 0) {
                                $latest = reset($payments);
                                echo date('M j, Y', strtotime($latest['paid_at']));
                            } else {
                                echo 'N/A';
                            }
                        ?>
                    </div>
                    <div class="stat-label">Last Payment</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (count($payments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Amount</th>
                        <th>Payment ID</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td>
                            <strong>Room <?php echo htmlspecialchars($p['room_number']); ?></strong>
                        </td>
                        <td class="amount">₹<?php echo htmlspecialchars($p['amount']); ?></td>
                        <td>
                            <span class="payment-id"><?php echo htmlspecialchars($p['razorpay_payment_id']); ?></span>
                        </td>
                        <td class="date"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($p['paid_at']))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-payments">
                <div class="no-payments-icon">💳</div>
                <h3>No Payment History</h3>
                <p>You haven't made any payments yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Simple animation for table rows
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
    });
</script>

</body>
</html>