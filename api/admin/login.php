<?php
require_once '../connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';

// Change this to your secure admin password
$ADMIN_PASSWORD = 'admin123';

if ($password !== $ADMIN_PASSWORD) {
    echo json_encode(['error' => 'Invalid admin password']);
    exit;
}

// Create or get admin user
$checkStmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
$checkResult = $checkStmt->execute();
$admin = $checkResult->fetchArray(SQLITE3_ASSOC);

if (!$admin) {
    $hashedPassword = password_hash($ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $insertStmt = $conn->prepare("INSERT INTO users (phone, password, role, invite_code) 
                                  VALUES ('admin@acquos.com', :password, 'admin', 'ADMIN001')");
    $insertStmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $insertStmt->execute();
    $adminId = $conn->lastInsertRowID();
} else {
    $adminId = $admin['id'];
}

// Create session
session_start();
$_SESSION['admin_id'] = $adminId;
$_SESSION['admin_logged_in'] = true;

echo json_encode([
    'success' => true, 
    'message' => 'Admin login successful',
    'redirect' => '/admin.html'
]);
?>