<?php
session_start();
use Razorpay\Api\Api;
require_once __DIR__ . '/vendor/autoload.php';
require_once 'connect.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$keyId = $_ENV['RAZORPAY_KEY_ID'] ?? null;
$keySecret = $_ENV['RAZORPAY_KEY_SECRET'] ?? null;

if (!$keyId || !$keySecret) {
    die("Razorpay credentials missing.");
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// ✅ Fetch assigned room
$stmt = $pdo->prepare("
    SELECT r.id AS room_id, r.room_number, r.type, r.price
    FROM users u
    LEFT JOIN rooms r ON u.room_id = r.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$assigned = $stmt->fetch();

// ✅ Fetch available rooms
$availableStmt = $pdo->prepare("SELECT * FROM rooms WHERE is_available = 1");
$availableStmt->execute();
$availableRooms = $availableStmt->fetchAll();

// ✅ Handle booking request
if (isset($_GET['room_id']) && !$assigned['room_id']) {
    $room_id = $_GET['room_id'];

    // Fetch room details
    $stmt = $pdo->prepare("SELECT price FROM rooms WHERE id = ? AND is_available = 1");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        die("Room not available.");
    }

    $price = $room['price'];
    $amountPaise = $price * 100;

    // ✅ Create pending bill
    $stmt = $pdo->prepare("INSERT INTO bills (user_id, room_id, amount, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$user_id, $room_id, $price]);
    $bill_id = $pdo->lastInsertId();

    $_SESSION['bill_id'] = $bill_id;
    $_SESSION['room_to_book'] = $room_id;

    // ✅ Create Razorpay order
  
    $api = new Api($keyId, $keySecret);

    $order = $api->order->create([
        'receipt' => 'room_booking_' . time(),
        'amount' => $amountPaise,
        'currency' => 'INR',
        'payment_capture' => 1
    ]);

    $_SESSION['razorpay_order_id'] = $order['id'];

    // ✅ Show Razorpay checkout
    echo <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Confirm Booking</title>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    </head>
    <body>
        <h2>Confirm Room Booking</h2>
        <button id="rzp-button">Pay ₹{$price}</button>

        <script>
        var options = {
            "key": "{$keyId}",
            "amount": "{$amountPaise}",
            "currency": "INR",
            "name": "Hostel Portal",
            "description": "Room Booking",
            "order_id": "{$order['id']}",
            "handler": function (response){
                window.location.href = "verify_payment.php?payment_id=" + response.razorpay_payment_id;
            },
            "prefill": {
                "name": "{$_SESSION['user']['name']}",
                "email": "{$_SESSION['user']['email']}"
            },
            "theme": {
                "color": "#3399cc"
            }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('rzp-button').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }
        </script>
    </body>
    </html>
    HTML;
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Room</title>
    <style>
        body { font-family: Arial; margin: 0; }
        .main { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        .vacate-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
        }
    </style>
    <script>
        function confirmVacate(roomId) {
            if (confirm("Are you sure you want to vacate this room?")) {
                window.location.href = "vacate_room.php?room_id=" + roomId;
            }
        }
    </script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main">
    <h2>My Room</h2>
    <?php if ($assigned && $assigned['room_number']): ?>
        <p><strong>Room Number:</strong> <?php echo $assigned['room_number']; ?></p>
        <p><strong>Type:</strong> <?php echo $assigned['type']; ?></p>
        <p><strong>Price:</strong> ₹<?php echo $assigned['price']; ?></p>
        <button class="vacate-btn" onclick="confirmVacate(<?php echo $assigned['room_id']; ?>)">Vacate Room</button>
    <?php else: ?>
        <p><em>No room assigned yet.</em></p>
    <?php endif; ?>

    <h2>Available Rooms</h2>
    <table>
        <tr>
            <th>Room Number</th><th>Type</th><th>Price</th><th>Action</th>
        </tr>
        <?php foreach ($availableRooms as $room): ?>
        <tr>
            <td><?php echo $room['room_number']; ?></td>
            <td><?php echo $room['type']; ?></td>
            <td>₹<?php echo $room['price']; ?></td>
            <td>
                <?php if (!$assigned['room_id']): ?>
                    <a href="book_room.php?room_id=<?php echo $room['id']; ?>">Book Room</a>
                <?php else: ?>
                    <em>Already booked</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
