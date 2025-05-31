<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "Kullanıcı ID gerekli."]);
    exit;
}

$sql = "SELECT c.product_id, p.name, p.price, p.image_url, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}
echo json_encode($cart_items);
?>
