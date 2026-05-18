<?php
require_once 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = (int)($data['user_id'] ?? 0);
$today = date('Y-m-d');

$checkStmt = $conn->prepare("SELECT id FROM checkins WHERE user_id = :user_id AND checkin_date = :date");
$checkStmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$checkStmt->bindValue(':date', $today, SQLITE3_TEXT);
$checkResult = $checkStmt->execute();

if ($checkResult->fetchArray()) {
    echo json_encode(['error' => 'Already checked in today']);
    exit;
}

$bonus = 500;
$update = $conn->prepare("UPDATE users SET balance = balance + :bonus, cumulative_income = cumulative_income + :bonus WHERE id = :id");
$update->bindValue(':bonus', $bonus, SQLITE3_FLOAT);
$update->bindValue(':id', $user_id, SQLITE3_INTEGER);
$update->execute();

$checkinStmt = $conn->prepare("INSERT INTO checkins (user_id, checkin_date, bonus) VALUES (:user_id, :date, :bonus)");
$checkinStmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$checkinStmt->bindValue(':date', $today, SQLITE3_TEXT);
$checkinStmt->bindValue(':bonus', $bonus, SQLITE3_FLOAT);
$checkinStmt->execute();

echo json_encode(['success' => true, 'bonus' => $bonus]);
?>