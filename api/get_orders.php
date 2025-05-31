<?php
session_start();
require_once 'config.php'; // Veritabanı bağlantısı
require_once 'functions.php'; // Giriş kontrolü

header('Content-Type: application/json');

// GET isteği ile gelen verileri kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // Kullanıcı giriş kontrolü
    if (!isLoggedIn()) {
        echo json_encode(['error' => 'Giriş yapmalısınız.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Kullanıcının siparişlerini veritabanından çek
    $stmt = $mysqli->prepare("SELECT id, order_date, total, order_status, payment_method FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];

    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
    }

    $stmt->close();

    // JSON formatında yanıt döndür
    echo json_encode(['orders' => $orders]);
} else {
    echo json_encode(['error' => 'Geçersiz istek.']);
}
?>
