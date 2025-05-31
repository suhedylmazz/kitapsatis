<?php
session_start();

// Projenin köküne çıkıp config ve fonksiyonları include ediyoruz
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

global $mysqli;  // config.php içinde tanımlı MySQL bağlantımızı kullanabilmek için

// JSON yanıt vereceğiz
header('Content-Type: application/json; charset=utf-8');

// Yalnızca POST kabul ediliyor
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Ham gövdeyi oku ve decode et
$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? intval($input['product_id']) : 0;
$quantity   = isset($input['quantity'])   ? intval($input['quantity'])   : 1;

if ($product_id <= 0 || $quantity < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product_id or quantity']);
    exit;
}

// Ürünü DB'den çek
$stmt = $mysqli->prepare("
    SELECT id, name, price, discount_percent, category_id
    FROM products
    WHERE id = ?
");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database prepare error']);
    exit;
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// İndirimli fiyatı hesapla
$originalPrice   = (float)$product['price'];
$discountPercent = (float)$product['discount_percent'];
$priceToCart     = $discountPercent > 0
                   ? $originalPrice * (1 - $discountPercent / 100)
                   : $originalPrice;

// Kategori adı
$category = '';
if ($product['category_id']) {
    $stmt = $mysqli->prepare("SELECT category_name FROM categories WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $product['category_id']);
        $stmt->execute();
        $catRes = $stmt->get_result();
        if ($cat = $catRes->fetch_assoc()) {
            $category = $cat['category_name'];
        }
        $stmt->close();
    }
}

// Sepeti başlat veya güncelle
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'product_id' => $product_id,
        'name'       => $product['name'],
        'price'      => round($priceToCart, 2),
        'quantity'   => $quantity,
        'category'   => $category
    ];
}

// Başarılı yanıt
echo json_encode([
    'success' => true,
    'cart'    => $_SESSION['cart']
]);