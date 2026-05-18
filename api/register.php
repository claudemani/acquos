<?php
require_once 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';
$inviteCode = $data['inviteCode'] ?? '';

if (empty($phone) || empty($password)) {
    echo json_encode(['error' => 'Phone and password required']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

$check = $conn->prepare("SELECT id FROM users WHERE phone = :phone");
$check->bindValue(':phone', $phone, SQLITE3_TEXT);
$result = $check->execute();
if ($result->fetchArray()) {
    echo json_encode(['error' => 'User already exists']);
    exit;
}

$invite_code = substr($phone, -6) . substr(md5(uniqid()), 0, 6);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$invited_by = 'NULL';
if (!empty($inviteCode)) {
    $refStmt = $conn->prepare("SELECT id FROM users WHERE invite_code = :code");
    $refStmt->bindValue(':code', $inviteCode, SQLITE3_TEXT);
    $refResult = $refStmt->execute();
    if ($ref = $refResult->fetchArray()) {
        $invited_by = $ref['id'];
    }
}

$insert = $conn->prepare("INSERT INTO users (phone, password, invite_code, invited_by, balance) 
                          VALUES (:phone, :password, :invite_code, $invited_by, 1000)");
$insert->bindValue(':phone', $phone, SQLITE3_TEXT);
$insert->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
$insert->bindValue(':invite_code', $invite_code, SQLITE3_TEXT);
$insert->execute();

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $conn->lastInsertRowID(),
        'phone' => $phone,
        'balance' => 1000,
        'invite_code' => $invite_code
    ]
]);
?>