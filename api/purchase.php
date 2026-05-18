<?php
require_once 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = (int)($data['user_id'] ?? 0);
$product_id = (int)($data['product_id'] ?? 0);

$productStmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$productStmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
$productResult = $productStmt->execute();
$product = $productResult->fetchArray(SQLITE3_ASSOC);

if (!$product) {
    echo json_encode(['error' => 'Product not found']);
    exit;
}

$userStmt = $conn->prepare("SELECT balance, invited_by FROM users WHERE id = :id");
$userStmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$userResult = $userStmt->execute();
$user = $userResult->fetchArray(SQLITE3_ASSOC);

if ($user['balance'] < $product['price']) {
    echo json_encode(['error' => 'Insufficient balance']);
    exit;
}

$update = $conn->prepare("UPDATE users SET balance = balance - :price WHERE id = :id");
$update->bindValue(':price', $product['price'], SQLITE3_FLOAT);
$update->bindValue(':id', $user_id, SQLITE3_INTEGER);
$update->execute();

$ref = 'PRCH' . time() . rand(1000, 9999);
$txStmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, status, reference) 
                          VALUES (:user_id, 'purchase', :amount, 'completed', :ref)");
$txStmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$txStmt->bindValue(':amount', $product['price'], SQLITE3_FLOAT);
$txStmt->bindValue(':ref', $ref, SQLITE3_TEXT);
$txStmt->execute();

$invStmt = $conn->prepare("INSERT INTO investments (user_id, product_id, product_name, amount, daily_income) 
                          VALUES (:user_id, :product_id, :name, :amount, :daily)");
$invStmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$invStmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
$invStmt->bindValue(':name', $product['name'], SQLITE3_TEXT);
$invStmt->bindValue(':amount', $product['price'], SQLITE3_FLOAT);
$invStmt->bindValue(':daily', $product['daily_income'], SQLITE3_FLOAT);
$invStmt->execute();

if ($user['invited_by']) {
    $commission = $product['price'] * 0.36;
    $commStmt = $conn->prepare("UPDATE users SET balance = balance + :comm WHERE id = :id");
    $commStmt->bindValue(':comm', $commission, SQLITE3_FLOAT);
    $commStmt->bindValue(':id', $user['invited_by'], SQLITE3_INTEGER);
    $commStmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Purchase successful']);
?>