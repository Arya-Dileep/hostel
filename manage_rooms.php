<?php
session_start();
require_once 'connect.php';

// ✅ Restrict access to admin only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ✅ Ensure upload folder exists
$uploadDir = __DIR__ . '/uploads/rooms/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Handle activation/deactivation
if (isset($_GET['toggle_id'])) {
    $roomId = $_GET['toggle_id'];
    $stmt = $pdo->prepare("SELECT status FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $current = $stmt->fetchColumn();
    $newStatus = ($current === 'active') ? 'inactive' : 'active';
    $pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?")->execute([$newStatus, $roomId]);
    header("Location: manage_rooms.php");
    exit;
}

// ✅ Handle new room addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_number'])) {
    $room_number = $_POST['room_number'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $status = $_POST['status'];
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = 'uploads/rooms/' . $filename; // relative path for browser
        }
    }

    $stmt = $pdo->prepare("INSERT INTO rooms (room_number, type, price, is_available, status, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$room_number, $type, $price, $is_available, $status, $imagePath]);
    header("Location: manage_rooms.php");
    exit;
}

// ✅ Handle image update for existing room
if (isset($_POST['update_image_id']) && !empty($_FILES['new_image']['name'])) {
    $roomId = $_POST['update_image_id'];
    $filename = time() . '_' . basename($_FILES['new_image']['name']);
    $target = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['new_image']['tmp_name'], $target)) {
        $imagePath = 'uploads/rooms/' . $filename; // relative path
        $stmt = $pdo->prepare("UPDATE rooms SET image = ? WHERE id = ?");
        $stmt->execute([$imagePath, $roomId]);
    }

    header("Location: manage_rooms.php");
    exit;
}

// ✅ Fetch all rooms
$stmt = $pdo->query("
    SELECT r.*, u.name AS booked_by
    FROM rooms r
    LEFT JOIN users u ON r.id = u.room_id
    ORDER BY r.id ASC
");
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Rooms</title>
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
            max-width: 1400px;
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
            content: "🏨";
            font-size: 24px;
        }
        h2 {
            color: #374151;
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
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
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .availability-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .available {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .unavailable {
            background-color: #fef3c7;
            color: #92400e;
        }
        .room-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }
        .no-image {
            width: 80px;
            height: 60px;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }
        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
        }
        .btn-outline:hover {
            background-color: #f9fafb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input {
            width: auto;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .image-upload {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: #f9fafb;
            transition: border-color 0.3s;
        }
        .image-upload:hover {
            border-color: #3b82f6;
        }
        .image-preview {
            max-width: 150px;
            max-height: 100px;
            margin-top: 10px;
            border-radius: 6px;
            display: none;
        }
        .action-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .card {
                padding: 20px;
            }
            th, td {
                padding: 10px 8px;
                font-size: 14px;
            }
            .action-cell {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container">
    <div class="card">
        <h1>Room Management</h1>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room Number</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Booked By</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><strong>#<?php echo $room['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($room['type']); ?></td>
                        <td><strong>₹<?php echo htmlspecialchars($room['price']); ?></strong></td>
                        <td>
                            <span class="availability-badge <?php echo $room['is_available'] ? 'available' : 'unavailable'; ?>">
                                <?php echo $room['is_available'] ? 'Yes' : 'No'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $room['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($room['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $room['booked_by'] ?? '—'; ?></td>
                        <td>
                            <?php if (!empty($room['image']) && file_exists(__DIR__ . '/' . $room['image'])): ?>
                                <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="Room Image" class="room-image">
                            <?php else: ?>
                                <div class="no-image">No image</div>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data" style="margin-top:8px;">
                                <input type="hidden" name="update_image_id" value="<?php echo $room['id']; ?>">
                                <input type="file" name="new_image" accept="image/*" style="font-size:12px; padding:6px;" required>
                                <button type="submit" class="btn btn-outline" style="margin-top:5px; padding:6px 12px;">Upload</button>
                            </form>
                        </td>
                        <td class="action-cell">
                            <a href="?toggle_id=<?php echo $room['id']; ?>" class="btn <?php echo $room['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>">
                                <?php echo $room['status'] === 'active' ? '⏸️ Deactivate' : '▶️ Activate'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Add New Room</h2>
        <form method="POST" enctype="multipart/form-data" id="roomForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="room_number">Room Number *</label>
                    <input type="text" id="room_number" name="room_number" required>
                </div>
                
                <div class="form-group">
                    <label for="type">Room Type *</label>
                    <select id="type" name="type" required>
                        <option value="Single">Single</option>
                        <option value="Double">Double</option>
                        <option value="Dormitary">Dormitary</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (₹) *</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_available" name="is_available" checked>
                    <label for="is_available" style="margin:0;">Available for booking</label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Room Image</label>
                <div class="image-upload">
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    <p style="color: #6b7280; margin-top: 8px; font-size: 14px;">Click to upload room image</p>
                    <img id="imagePreview" class="image-preview" alt="Image preview">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">➕ Add Room</button>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Add some interactivity to the table rows
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });
</script>

</body>
</html>