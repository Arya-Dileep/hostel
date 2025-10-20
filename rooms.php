<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch assigned room
$stmt = $pdo->prepare("
    SELECT r.room_number, r.type, r.price
    FROM users u
    LEFT JOIN rooms r ON u.room_id = r.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$assigned = $stmt->fetch();

// Fetch available rooms
$availableStmt = $pdo->prepare("SELECT * FROM rooms WHERE is_available = TRUE");
$availableStmt->execute();
$availableRooms = $availableStmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Room</title>
    <style>
        body { font-family: Arial; margin: 0; }
        .main { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main">
    <h2>My Room</h2>
    <?php if ($assigned && $assigned['room_number']): ?>
        <p><strong>Room Number:</strong> <?php echo $assigned['room_number']; ?></p>
        <p><strong>Type:</strong> <?php echo $assigned['type']; ?></p>
        <p><strong>Price:</strong> ₹<?php echo $assigned['price']; ?></p>
    <?php else: ?>
        <p><em>No room assigned yet.</em></p>
    <?php endif; ?>

    <h2>Available Rooms</h2>
    <table>
        <tr>
            <th>Room Number</th><th>Type</th><th>Price</th>
        </tr>
        <?php foreach ($availableRooms as $room): ?>
        <tr>
            <td><?php echo $room['room_number']; ?></td>
            <td><?php echo $room['type']; ?></td>
            <td>₹<?php echo $room['price']; ?></td>
            <td>
    <a href="book_room.php?room_id=<?php echo $room['id']; ?>">Book Room</a>
</td>

        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
