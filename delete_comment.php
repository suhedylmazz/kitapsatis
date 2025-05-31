<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['comment_id']) || !isset($_GET['product_id'])) {
    die("Geçersiz istek.");
}

$comment_id = intval($_GET['comment_id']);
$product_id = intval($_GET['product_id']);

// Yorum sahibini kontrol edelim
$stmt = $mysqli->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($comment_user_id);
if (!$stmt->fetch()) {
    die("Yorum bulunamadı.");
}
$stmt->close();

// Sadece yorum sahibi veya admin silme yetkisine sahip olsun
if ($_SESSION['user_id'] != $comment_user_id && (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')) {
    die("Bu yorumu silemezsiniz.");
}

// Yorum silme işlemi
$stmtDel = $mysqli->prepare("DELETE FROM comments WHERE id = ?");
$stmtDel->bind_param("i", $comment_id);
if ($stmtDel->execute()) {
    $stmtDel->close();
    header("Location: product_detail.php?id=" . $product_id);
    exit;
} else {
    die("Yorum silinirken hata oluştu: " . $stmtDel->error);
}
?>
