<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "Kullanıcı ID gerekli."]);
    exit;
}

$sql = "SELECT f.product_id, p.name, p.price, p.image_url
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        WHERE f.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
echo json_encode($favorites);
?>
