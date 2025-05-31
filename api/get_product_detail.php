<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Ürün ID belirtilmedi.']);
    exit;
}

$product_id = intval($_GET['id']);
$product = getProduct($product_id);

if ($product) {
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı.']);
}
?>
