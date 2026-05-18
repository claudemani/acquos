<?php
require_once 'connect.php';

$user_id = (int)($_GET['user_id'] ?? 0);

$userStmt = $conn->prepare("SELECT balance, cumulative_income FROM users WHERE id = :id");
$userStmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$userResult = $userStmt->execute();
$user = $userResult->fetchArray(SQLITE3_ASSOC);

$teamStmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE invited_by = :id");
$teamStmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$teamResult = $teamStmt->execute();
$team = $teamResult->fetchArray(SQLITE3_ASSOC);

echo json_encode([
    'balance' => $user['balance'] ?? 0,
    'cumulative_income' => $user['cumulative_income'] ?? 0,
    'teamCount' => $team['count'] ?? 0
]);
?>