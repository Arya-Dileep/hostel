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
    <style>
        body { font-family: Arial; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        .download-btn {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        }
        .main {
            margin-left: 200px;
            padding: 20px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main">
    <h2>Payment History</h2>

    <a href="download_payments.php" class="download-btn">Download PDF</a>

    <table>
        <tr>
            <th>Room</th><th>Amount</th><th>Payment ID</th><th>Date</th>
        </tr>
        <?php foreach ($payments as $p): ?>
        <tr>
            <td><?php echo htmlspecialchars($p['room_number']); ?></td>
            <td>₹<?php echo htmlspecialchars($p['amount']); ?></td>
            <td><?php echo htmlspecialchars($p['razorpay_payment_id']); ?></td>
            <td><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($p['paid_at']))); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
