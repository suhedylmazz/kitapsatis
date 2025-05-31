<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['keyword'])) {
    echo json_encode(['success' => false, 'message' => 'Arama kelimesi belirtilmedi.']);
    exit;
}

$keyword = $_GET['keyword'];
$products = searchProducts($keyword);

echo json_encode(['success' => true, 'products' => $products]);
?>
