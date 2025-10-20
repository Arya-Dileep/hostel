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
    SELECT r.room_number, r.type, r.price, r.image
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Room - Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #a466eaff 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .dashboard-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .dashboard-header p {
            color: #666;
            font-size: 1.1em;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 3px solid #ac66eaff;
            padding-bottom: 10px;
        }

        .room-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #ac66eaff;
        }

        .info-item strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .info-item span {
            color: #666;
            font-size: 1.1em;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .room-card:hover {
            transform: translateY(-10px);
            border-color: #ac66eaff;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .room-image:hover {
            transform: scale(1.05);
        }

        .room-details {
            padding: 20px;
        }

        .room-number {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .room-type {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .room-price {
            font-size: 1.4em;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .book-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .book-btn:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .no-room {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80vh;
            object-fit: contain;
            animation: zoomIn 0.3s ease;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .close:hover {
            color: #bbb;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-assigned {
            background: #d4edda;
            color: #155724;
        }

        .status-available {
            background: #d1ecf1;
            color: #0c5460;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .rooms-grid {
                grid-template-columns: 1fr;
            }
            
            .room-info {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="dashboard-header">
            <h1>Student Dashboard</h1>
            <p>Manage your room assignments and explore available options</p>
        </div>

        <!-- Current Room Assignment -->
        <div class="card">
            <h2>My Current Room</h2>
            <?php if ($assigned && $assigned['room_number']): ?>
                <div class="status-badge status-assigned">Assigned</div>
                <div class="room-info">
                    <div class="info-item">
                        <strong>Room Number</strong>
                        <span><?php echo htmlspecialchars($assigned['room_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Room Type</strong>
                        <span><?php echo htmlspecialchars($assigned['type']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Price</strong>
                        <span>₹<?php echo number_format($assigned['price'], 2); ?> / month</span>
                    </div>
                </div>
                <?php if (!empty($assigned['image'])): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <img src="<?php echo htmlspecialchars($assigned['image']); ?>" 
                             alt="Assigned Room" 
                             class="room-image"
                             onclick="openModal('<?php echo htmlspecialchars($assigned['image']); ?>')">
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-room">
                    <p>No room assigned yet. Browse available rooms below to book one.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Available Rooms -->
        <div class="card">
            <h2>Available Rooms</h2>
            <div class="status-badge status-available">Available for Booking</div>
            
            <?php if (count($availableRooms) > 0): ?>
                <div class="rooms-grid">
                    <?php foreach ($availableRooms as $room): ?>
                        <div class="room-card">
                            <?php if (!empty($room['image'])): ?>
                                <img src="<?php echo htmlspecialchars($room['image']); ?>" 
                                     alt="Room <?php echo htmlspecialchars($room['room_number']); ?>" 
                                     class="room-image"
                                     onclick="openModal('<?php echo htmlspecialchars($room['image']); ?>')">
                            <?php else: ?>
                                <div style="height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #666;">
                                    No Image Available
                                </div>
                            <?php endif; ?>
                            
                            <div class="room-details">
                                <div class="room-number">Room <?php echo htmlspecialchars($room['room_number']); ?></div>
                                <div class="room-type"><?php echo htmlspecialchars($room['type']); ?></div>
                                <div class="room-price">₹<?php echo number_format($room['price'], 2); ?>/month</div>
                                
                                <?php if (!empty($room['description'])): ?>
                                    <p style="color: #666; margin-bottom: 15px; font-size: 0.9em;">
                                        <?php echo htmlspecialchars($room['description']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <a href="book_room.php?room_id=<?php echo $room['id']; ?>" class="book-btn">
                                    Book This Room
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-room">
                    <p>No rooms available at the moment. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        // Modal functionality
        function openModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = imageSrc;
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal when clicking outside the image
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Add loading animation to images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.room-image');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
                // Set initial opacity for fade-in effect
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
            });
        });
    </script>
</body>
</html>