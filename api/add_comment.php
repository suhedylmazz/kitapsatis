<?php
// api/add_comment.php

header('Content-Type: application/json');
require_once "config.php";

// Gelen JSON veriyi oku
$data = json_decode(file_get_contents('php://input'), true);

$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
$user_id    = isset($data['user_id'])    ? intval($data['user_id'])    : 0;
$comment_text = isset($data['comment_text']) ? trim($data['comment_text']) : '';

// Alan kontrolü
if (!$product_id || !$user_id || empty($comment_text)) {
    echo json_encode([
        'success' => false,
        'message' => 'Tüm alanlar zorunludur.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Yorum ekleme
$stmt = $conn->prepare("INSERT INTO comments (product_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
if ($stmt) {
    $stmt->bind_param("iis", $product_id, $user_id, $comment_text);
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Yorum başarıyla eklendi.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Yorum eklenirken hata oluştu.'
        ], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Sorgu hazırlanamadı: ' . $conn->error
    ], JSON_UNESCAPED_UNICODE);
}
?>
