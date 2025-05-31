<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config.php'; // Veritabanı bağlantısı

// Admin kontrolü: Sadece admin yetkisi olanlar ürünü silebilir.
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["error" => "Admin yetkisi gereklidir."]);
    exit;
}

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Ürünü silmek için prepared statement kullanıyoruz
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(["error" => "Sorgu hazırlama hatası: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Ürün başarıyla silindiğinde başarılı mesajı
        echo json_encode([
            "success" => true,
            "message" => "Ürün başarıyla silindi.",
            "product_id" => $product_id
        ]);
    } else {
        // Hata durumu
        echo json_encode(["error" => "Ürün silinemedi: " . $stmt->error]);
    }
    
    $stmt->close();
} else {
    // Eğer id parametresi yoksa hata mesajı döndür
    echo json_encode(["error" => "Ürün ID'si belirtilmemiş."]);
    exit;
}
?>
