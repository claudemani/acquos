<?php
require_once 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE phone = :phone");
$stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

unset($user['password']);
echo json_encode(['success' => true, 'user' => $user]);
?>