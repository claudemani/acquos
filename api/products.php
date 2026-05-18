<?php
require_once 'connect.php';

$result = $conn->query("SELECT * FROM products WHERE is_active = 1");
$products = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $row;
}
echo json_encode($products);
?>