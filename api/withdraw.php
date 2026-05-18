<?php
require_once 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = (int)($data['user_id'] ?? 0);
$amount = (float)($data['amount'] ?? 0);

if ($amount < 1000) {
    echo json_encode(['error' => 'Minimum withdrawal is 1,000 RWF']);
    exit;
}

$balanceStmt = $conn->prepare("SELECT balance FROM users WHERE id = :id");
$balanceStmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$balanceResult = $balanceStmt->execute();
$user = $balanceResult->fetchArray(SQLITE3_ASSOC);

if ($user['balance'] < $amount) {
    echo json_encode(['error' => 'Insufficient balance']);
    exit;
}

$update = $conn->prepare("UPDATE users SET balance = balance - :amount WHERE id = :id");
$update->bindValue(':amount', $amount, SQLITE3_FLOAT);
$update->bindValue(':id', $user_id, SQLITE3_INTEGER);
$update->execute();

$ref = 'WDR' . time() . rand(1000, 9999);
$txStmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, status, reference) 
                          VALUES (:user_id, 'withdraw', :amount, 'pending', :ref)");
$txStmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$txStmt->bindValue(':amount', $amount, SQLITE3_FLOAT);
$txStmt->bindValue(':ref', $ref, SQLITE3_TEXT);
$txStmt->execute();

echo json_encode(['success' => true, 'message' => 'Withdrawal request submitted']);
?>