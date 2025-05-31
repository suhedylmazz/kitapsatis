<?php
// api/delete_comment.php

header('Content-Type: application/json');
require_once "config.php";

// Gelen JSON veriyi oku
$data = json_decode(file_get_contents('php://input'), true);

$comment_id = isset($data['comment_id']) ? intval($data['comment_id']) : 0;
$user_id    = isset($data['user_id'])    ? intval($data['user_id'])    : 0;
$user_role  = isset($data['role'])        ? trim($data['role'])        : ''; // "admin" olabilir

// Alan kontrolü
if (!$comment_id || !$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Yorum ID ve Kullanıcı ID gerekli.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Yorumu bul ve sahibini getir
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($comment_user_id);
if (!$stmt->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'Yorum bulunamadı.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->close();

// Yorum sahibi veya admin kontrolü
if ($user_id != $comment_user_id && $user_role !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Bu yorumu silme yetkiniz yok.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Yorum silme işlemi
$stmtDel = $conn->prepare("DELETE FROM comments WHERE id = ?");
$stmtDel->bind_param("i", $comment_id);
if ($stmtDel->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Yorum başarıyla silindi.'
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Yorum silinirken hata oluştu.'
    ], JSON_UNESCAPED_UNICODE);
}
$stmtDel->close();
?>
