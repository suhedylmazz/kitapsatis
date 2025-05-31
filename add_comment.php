<?php
session_start();
require_once 'config.php'; // Bu dosyada $mysqli tanımlı olmalı

// Giriş yapmamışsa yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    // Formdaki textarea'nın adı "comment_text" olmalı
    $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
    
    if (empty($comment_text)) {
        die("Lütfen yorumunuzu girin.");
    }
    
    $user_id = $_SESSION['user_id'];
    
    $stmt = $mysqli->prepare("INSERT INTO comments (product_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        die("Prepare error: " . $mysqli->error);
    }
    
    $stmt->bind_param("iis", $product_id, $user_id, $comment_text);
    if ($stmt->execute()) {
        header("Location: product.php?id=" . $product_id);
        exit;
    } else {
        die("Yorum eklenemedi: " . $stmt->error);
    }
    $stmt->close();
}
?>
