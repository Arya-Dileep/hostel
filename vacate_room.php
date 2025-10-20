<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$room_id = $_GET['room_id'] ?? null;

if (!$room_id) {
    die("Room ID missing.");
}

// ✅ Remove room assignment from user
$stmt = $pdo->prepare("UPDATE users SET room_id = NULL WHERE id = ?");
$stmt->execute([$user_id]);

// ✅ Mark room as available
$stmt = $pdo->prepare("UPDATE rooms SET is_available = 1 WHERE id = ?");
$stmt->execute([$room_id]);

header("Location: book_room.php");
exit;
