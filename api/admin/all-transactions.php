<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../connect.php';

$result = $conn->query("SELECT t.*, u.phone 
                        FROM transactions t 
                        JOIN users u ON t.user_id = u.id 
                        ORDER BY t.created_at DESC 
                        LIMIT 100");

$transactions = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $transactions[] = $row;
}

echo json_encode($transactions);
?>