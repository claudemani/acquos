<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$transaction_id = (int)($data['transaction_id'] ?? 0);

// Update transaction status
$updateStmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE id = :id");
$updateStmt->bindValue(':id', $transaction_id, SQLITE3_INTEGER);
$updateStmt->execute();

echo json_encode(['success' => true, 'message' => 'Withdrawal approved']);
?>