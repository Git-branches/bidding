<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bidding_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1;
}

// Check if user is client
function isClient() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 2;
}

// Redirect based on user type
function redirectBasedOnUserType() {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
        exit();
    } elseif (isClient()) {
        header("Location: client/dashboard.php");
        exit();
    }
}

// Format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Get user money (decoded)
function getUserMoney($encodedMoney) {
    return base64_decode($encodedMoney);
}
?>