<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? null;
$product_id = $data['product_id'] ?? null;

if (!$user_id || !$product_id) {
    echo json_encode(["success" => false, "message" => "Eksik veri."]);
    exit;
}

$sql = "INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Favorilere eklendi."]);
} else {
    echo json_encode(["success" => false, "message" => "Favori eklenemedi."]);
}
?>
