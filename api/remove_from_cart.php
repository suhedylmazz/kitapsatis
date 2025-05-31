<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$user_id = $_GET['user_id'] ?? null;
$product_id = $_GET['product_id'] ?? null;

if (!$user_id || !$product_id) {
    echo json_encode(["success" => false, "message" => "Veri eksik."]);
    exit;
}

$sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Ürün sepetten silindi."]);
} else {
    echo json_encode(["success" => false, "message" => "Silme işlemi başarısız."]);
}
?>
