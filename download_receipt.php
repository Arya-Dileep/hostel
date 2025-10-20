<?php
session_start();
$payment_id = $_POST['payment_id'] ?? null;

if (!$payment_id) {
    die("Invalid request.");
}

echo "<h2>Receipt for Payment ID: $payment_id</h2>";
echo "<p>This is a placeholder. You can generate a PDF or styled HTML receipt here.</p>";
