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
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            body {
                background-color: #f5f7fa;
                color: #333;
                line-height: 1.6;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            .card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                padding: 30px;
                margin-bottom: 25px;
            }
            h1, h2 {
                color: #2c3e50;
                margin-bottom: 20px;
            }
            h1 {
                font-size: 28px;
                border-bottom: 2px solid #3498db;
                padding-bottom: 10px;
            }
            h2 {
                font-size: 22px;
                margin-top: 30px;
            }
            .room-info {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin-bottom: 20px;
            }
            .info-item {
                flex: 1;
                min-width: 150px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                text-align: center;
            }
            .info-label {
                font-size: 14px;
                color: #7f8c8d;
                margin-bottom: 5px;
            }
            .info-value {
                font-size: 18px;
                font-weight: 600;
                color: #2c3e50;
            }
            .btn {
                display: inline-block;
                padding: 12px 25px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                border: none;
                font-size: 16px;
            }
            .btn-primary {
                background-color: #3498db;
                color: white;
            }
            .btn-primary:hover {
                background-color: #2980b9;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
            }
            .btn-danger {
                background-color: #e74c3c;
                color: white;
            }
            .btn-danger:hover {
                background-color: #c0392b;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
            }
            .btn-disabled {
                background-color: #bdc3c7;
                color: #7f8c8d;
                cursor: not-allowed;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                padding: 15px;
                text-align: left;
                border-bottom: 1px solid #ecf0f1;
            }
            th {
                background-color: #3498db;
                color: white;
                font-weight: 600;
            }
            tr:hover {
                background-color: #f8f9fa;
            }
            .status-badge {
                display: inline-block;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
            }
            .status-available {
                background-color: #d4edda;
                color: #155724;
            }
            .status-booked {
                background-color: #f8d7da;
                color: #721c24;
            }
            .no-room {
                text-align: center;
                padding: 30px;
                color: #7f8c8d;
                font-style: italic;
            }
            .payment-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                align-items: center;
                justify-content: center;
            }
            .modal-content {
                background: white;
                border-radius: 12px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                text-align: center;
            }
            @media (max-width: 768px) {
                .room-info {
                    flex-direction: column;
                }
                .info-item {
                    min-width: 100%;
                }
                table {
                    display: block;
                    overflow-x: auto;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1>Confirm Room Booking</h1>
                <div class="room-info">
                    <div class="info-item">
                        <div class="info-label">Room Price</div>
                        <div class="info-value">₹{$price}</div>
                    </div>
                </div>
                <button id="rzp-button" class="btn btn-primary">Pay ₹{$price}</button>
            </div>
        </div>

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
                "color": "#3498db"
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f5f7fa;
            color: #333;
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 25px;
        }
        h1, h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 28px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            font-size: 22px;
            margin-top: 30px;
        }
        .room-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        .info-label {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }
        .btn-disabled {
            background-color: #bdc3c7;
            color: #7f8c8d;
            cursor: not-allowed;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-available {
            background-color: #d4edda;
            color: #155724;
        }
        .status-booked {
            background-color: #f8d7da;
            color: #721c24;
        }
        .no-room {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-style: italic;
        }
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        @media (max-width: 768px) {
            .room-info {
                flex-direction: column;
            }
            .info-item {
                min-width: 100%;
            }
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="card">
        <h1>My Room</h1>
        <?php if ($assigned && $assigned['room_number']): ?>
            <div class="room-info">
                <div class="info-item">
                    <div class="info-label">Room Number</div>
                    <div class="info-value"><?php echo $assigned['room_number']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Type</div>
                    <div class="info-value"><?php echo $assigned['type']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Price</div>
                    <div class="info-value">₹<?php echo $assigned['price']; ?></div>
                </div>
            </div>
            <button class="btn btn-danger" onclick="confirmVacate(<?php echo $assigned['room_id']; ?>)">Vacate Room</button>
        <?php else: ?>
            <div class="no-room">
                <p>No room assigned yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Available Rooms</h2>
        <?php if (count($availableRooms) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableRooms as $room): ?>
                    <tr>
                        <td><?php echo $room['room_number']; ?></td>
                        <td><?php echo $room['type']; ?></td>
                        <td>₹<?php echo $room['price']; ?></td>
                        <td><span class="status-badge status-available">Available</span></td>
                        <td>
                            <?php if (!$assigned['room_id']): ?>
                                <a href="book_room.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">Book Room</a>
                            <?php else: ?>
                                <span class="status-badge status-booked">Want to Book?</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-room">
                <p>No rooms available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function confirmVacate(roomId) {
        if (confirm("Are you sure you want to vacate this room?")) {
            window.location.href = "vacate_room.php?room_id=" + roomId;
        }
    }
</script>

</body>
</html>