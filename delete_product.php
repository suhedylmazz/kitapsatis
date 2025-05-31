<?php
session_start();
require_once 'config.php';

// Admin kontrolü: Sadece admin yetkisi olanlar ürünü silebilir.
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    // Ürünü silmek için prepared statement kullanıyoruz
    $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    if (!$stmt) {
        die("Sorgu hazırlama hatası: " . $mysqli->error);
    }
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        // Ürün başarıyla silindiğinde admin paneline yönlendir
        header("Location: admin.php");
        exit;
    } else {
        die("Ürün silinemedi: " . $stmt->error);
    }
    $stmt->close();
} else {
    // Eğer id parametresi yoksa, admin paneline yönlendir
    header("Location: admin.php");
    exit;
}
?>
