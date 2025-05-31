<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['category'])) {
    echo json_encode(['success' => false, 'message' => 'Kategori belirtilmedi.']);
    exit;
}

$category = $_GET['category'];
$products = getProductsByCategory($category);

echo json_encode(['success' => true, 'products' => $products]);
?>
