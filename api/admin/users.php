<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../connect.php';

$result = $conn->query("SELECT id, phone, balance, cumulative_income, invite_code, created_at 
                        FROM users WHERE role = 'user' ORDER BY created_at DESC");

$users = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // Get referral count for each user
    $refStmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE invited_by = :id");
    $refStmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
    $refResult = $refStmt->execute();
    $refs = $refResult->fetchArray(SQLITE3_ASSOC);
    
    $row['referrals'] = $refs['count'] ?? 0;
    $users[] = $row;
}

echo json_encode($users);
?>