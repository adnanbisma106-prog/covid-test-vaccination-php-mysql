<?php
/**
 * COVID-19 Test & Vaccination Booking System (ORS)
 * Database Connection & Global Helper Functions
 * Framework: PHP 7.4+ / PHP 8.x with MySQLi & PDO
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Standard XAMPP / WAMP defaults)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'covid_ors_db');

// Establish Connection
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// If database doesn't exist yet, attempt automatic creation
if ($conn->connect_error) {
    $temp_conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
    if (!$temp_conn->connect_error) {
        $temp_conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $temp_conn->close();
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
}

// Global Auth Checkers
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getLoggedInUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'patient',
        'avatar' => $_SESSION['user_avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&h=120&fit=crop'
    ];
}

function requireAuth($allowedRoles = []) {
    if (!isLoggedIn()) {
        header("Location: index.php?msg=Please login to access this portal&type=warning");
        exit;
    }
    if (!empty($allowedRoles)) {
        $currentRole = $_SESSION['user_role'] ?? '';
        if (!in_array($currentRole, $allowedRoles)) {
            header("Location: index.php?msg=Unauthorized access for your account type&type=danger");
            exit;
        }
    }
}

// Flash Message Helper
function setFlash($message, $type = 'success') {
    $_SESSION['flash_msg'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlash() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
        return ['message' => $msg, 'type' => $type];
    }
    return null;
}

// Log Audit Action
function logAudit($conn, $user, $action) {
    if (!$conn || $conn->connect_error) return;
    $id = 'log_' . time() . '_' . rand(100, 999);
    $time = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO audit_logs (id, timestamp, user, action) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $id, $time, $user, $action);
        $stmt->execute();
        $stmt->close();
    }
}
?>
