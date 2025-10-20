<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$keyId = $_ENV['RAZORPAY_KEY_ID'];
$keySecret = $_ENV['RAZORPAY_KEY_SECRET'];

use Razorpay\Api\Api;
$api = new Api($keyId, $keySecret);

$payment_id = $_GET['payment_id'] ?? null;
$bill_id = $_SESSION['bill_id'] ?? null;
$room_id = $_SESSION['room_to_book'] ?? null;
$user_id = $_SESSION['user']['id'];

if (!$payment_id || !$bill_id || !$room_id) {
    die("Missing payment or booking details.");
}

$payment = $api->payment->fetch($payment_id);
$amount = $payment->amount / 100;

$pdo = new PDO("mysql:host=localhost;dbname=hostel;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// ✅ Update bill status
$pdo->prepare("UPDATE bills SET status = 'paid' WHERE id = ?")->execute([$bill_id]);

// ✅ Insert payment record
$pdo->prepare("INSERT INTO payments (bill_id, razorpay_payment_id, amount) VALUES (?, ?, ?)")
    ->execute([$bill_id, $payment_id, $amount]);

// ✅ Assign room to user
$pdo->prepare("UPDATE users SET room_id = ? WHERE id = ?")->execute([$room_id, $user_id]);

// ✅ Mark room as unavailable
$pdo->prepare("UPDATE rooms SET is_available = 0 WHERE id = ?")->execute([$room_id]);

echo "<h2>✅ Payment successful!</h2><p>Your room has been booked.</p>";
?>
