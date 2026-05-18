<?php
require_once 'connect.php';

$user_id = (int)($_GET['user_id'] ?? 0);

$level1Stmt = $conn->prepare("SELECT id, phone, created_at FROM users WHERE invited_by = :id");
$level1Stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$level1Result = $level1Stmt->execute();

$level1 = [];
while ($row = $level1Result->fetchArray(SQLITE3_ASSOC)) {
    $level1[] = $row;
}

echo json_encode(['level1' => $level1]);
?>