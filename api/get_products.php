<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

// Kategori filtresi var mı diye bakıyoruz:
$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' 
    ? intval($_GET['category_id']) 
    : null;

// Temel sorgu
$sql = "SELECT id, name, price, description, image_url, category_id 
        FROM products";

// Eğer category_id geldiyse WHERE ekle
if ($categoryId !== null) {
    $sql .= " WHERE category_id = {$categoryId}";
}

$result = mysqli_query($conn, $sql);

$response = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // tip güvenliği için cast
        $row['id']          = (int)$row['id'];
        $row['price']       = (float)$row['price'];
        $row['category_id'] = (int)$row['category_id'];
        $response[] = $row;
    }
    echo json_encode(["products" => $response], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
      "error" => "Ürünler alınamadı: " . mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);
}
exit;
