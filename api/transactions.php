<?php
require_once 'connect.php';

$user_id = (int)($_GET['user_id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = :id ORDER BY created_at DESC LIMIT 50");
$stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$transactions = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $transactions[] = $row;
}

echo json_encode($transactions);
?>