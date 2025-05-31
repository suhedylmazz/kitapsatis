<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';  // Veritabanı bağlantısı
require_once 'functions.php';  // Giriş kontrolü (isLoggedIn vb.)

header('Content-Type: application/json');

// API sadece POST isteğiyle çalışacak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating']) && isset($_POST['product_id'])) {
    $rating = intval($_POST['rating']);  // Oy puanı
    $product_id = intval($_POST['product_id']);  // Ürün ID

    // Kullanıcı giriş kontrolü
    if (!isLoggedIn()) {
        echo json_encode(['error' => 'Giriş yapmalısınız.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Kullanıcının daha önce bu ürüne oy verip vermediğini kontrol et
    $checkStmt = $mysqli->prepare("SELECT rating FROM product_ratings WHERE product_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $product_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    if ($result->num_rows > 0) {
        // Mevcut oyu güncelle
        $updateStmt = $mysqli->prepare("UPDATE product_ratings SET rating = ? WHERE product_id = ? AND user_id = ?");
        $updateStmt->bind_param("iii", $rating, $product_id, $user_id);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // Yeni oy ekle
        $insertStmt = $mysqli->prepare("INSERT INTO product_ratings (product_id, user_id, rating) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iii", $product_id, $user_id, $rating);
        $insertStmt->execute();
        $insertStmt->close();
    }
    $checkStmt->close();

    // Güncellenmiş ortalama puan ve oy sayısını tekrar hesapla
    $avgStmt = $mysqli->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_votes FROM product_ratings WHERE product_id = ?");
    $avgStmt->bind_param("i", $product_id);
    $avgStmt->execute();
    $resultAvg = $avgStmt->get_result();
    $data = $resultAvg->fetch_assoc();
    $avgStmt->close();

    // JSON formatında yanıt döndür
    echo json_encode([
        'avgRating' => round($data['avg_rating'], 1),
        'totalVotes' => $data['total_votes']
    ]);
    exit;
} else {
    echo json_encode(['error' => 'Geçersiz istek.']);
    exit;
}
?>
 