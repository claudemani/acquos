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
                        WHERE t.type = 'withdraw' AND t.status = 'pending' 
                        ORDER BY t.created_at DESC");

$withdrawals = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $withdrawals[] = $row;
}

echo json_encode($withdrawals);
?>