<?php
session_start();
require_once 'connect.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    die("Unauthorized access.");
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

$html = '<h2>Payment History</h2>';
$html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%">';
$html .= '<tr><th>Room</th><th>Amount</th><th>Payment ID</th><th>Date</th></tr>';

foreach ($payments as $p) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($p['room_number']) . '</td>';
    $html .= '<td>₹' . htmlspecialchars($p['amount']) . '</td>';
    $html .= '<td>' . htmlspecialchars($p['razorpay_payment_id']) . '</td>';
    $html .= '<td>' . htmlspecialchars(date('d M Y, h:i A', strtotime($p['paid_at']))) . '</td>';
    $html .= '</tr>';
}

$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("payment_history.pdf", ["Attachment" => true]);
exit;
