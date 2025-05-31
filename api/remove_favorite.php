<?php
session_start();

// JSON yanıt üreteceğiz
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// 1) Yalnızca POST kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);
    exit;
}

// 2) Giriş kontrolü
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// 3) JSON gövdesini oku
$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? intval($input['product_id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing product_id'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
global $mysqli;

// 4) Veritabanından favoriyi sil
$stmt = $mysqli->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database prepare error'
    ]);
    exit;
}
$stmt->bind_param("ii", $user_id, $product_id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database execute error'
    ]);
    exit;
}
$stmt->close();

// 5) Başarılı yanıt
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Favorite removed'
]);