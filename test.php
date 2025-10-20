<?php
$roomId = $_POST['room_id'];
$imageName = $_FILES['room_image']['name'];
$imageTmp = $_FILES['room_image']['tmp_name'];
$targetDir = "uploads/";
$targetPath = $targetDir . basename($imageName);

// Move the file to your server
if (move_uploaded_file($imageTmp, $targetPath)) {
    // Connect to DB
    $conn = new mysqli("localhost", "username", "password", "your_database");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update image path
    $stmt = $conn->prepare("UPDATE rooms SET image = ? WHERE id = ?");
    $stmt->bind_param("si", $targetPath, $roomId);
    $stmt->execute();

    echo "Image updated successfully!";
} else {
    echo "Image upload failed.";
}
?>
