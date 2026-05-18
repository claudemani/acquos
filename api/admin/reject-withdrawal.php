<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$transaction_id = (int)($data['transaction_id'] ?? 0);

// Get transaction details
$txStmt = $conn->prepare("SELECT user_id, amount FROM transactions WHERE id = :id");
$txStmt->bindValue(':id', $transaction_id, SQLITE3_INTEGER);
$txResult = $txStmt->execute();
$transaction = $txResult->fetchArray(SQLITE3_ASSOC);

if ($transaction) {
    // Refund the money to user
    $refundStmt = $conn->prepare("UPDATE users SET balance = balance + :amount WHERE id = :user_id");
    $refundStmt->bindValue(':amount', $transaction['amount'], SQLITE3_FLOAT);
    $refundStmt->bindValue(':user_id', $transaction['user_id'], SQLITE3_INTEGER);
    $refundStmt->execute();
    
    // Update transaction status
    $updateStmt = $conn->prepare("UPDATE transactions SET status = 'rejected' WHERE id = :id");
    $updateStmt->bindValue(':id', $transaction_id, SQLITE3_INTEGER);
    $updateStmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Withdrawal rejected and refunded']);
?>