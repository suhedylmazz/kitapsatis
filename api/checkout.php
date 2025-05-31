<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config.php'; // Veritabanı bağlantısı
require_once '../functions.php'; // getProduct(), isLoggedIn() gibi fonksiyonlar

// Kullanıcı giriş yapmamışsa hata mesajı döndür
if (!isLoggedIn()) {
    echo json_encode(["error" => "Giriş yapmalısınız."]);
    exit;
}

// Sepet boşsa hata mesajı döndür
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode(["error" => "Sepetiniz boş."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ödeme yöntemi, POST üzerinden alınır
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Kredi Kartı';

// Siparişin orders tablosuna eklenmesi
$sql = "INSERT INTO orders (user_id, payment_method) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Veritabanı hatası: " . $conn->error]);
    exit;
}

$stmt->bind_param("is", $user_id, $payment_method);

if ($stmt->execute()) {
    // Sipariş başarıyla oluşturulduktan sonra oluşturulan siparişin id'sini alıyoruz
    $order_id = $stmt->insert_id;
    
    // Siparişin detaylarını kaydedelim (isteğe bağlı: order_items tablosuna sepeti ekleyelim)
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProduct($product_id);
        if ($product) {
            $price = $product['price'];
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_item->execute();
        }
    }

    // Sipariş oluşturulduktan sonra sepet temizleniyor
    unset($_SESSION['cart']);
    
    // Başarıyla işlem yapıldığını belirten JSON mesajı
    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "message" => "Sipariş başarıyla oluşturuldu.",
    ]);
} else {
    // Hata durumu
    echo json_encode(["error" => "Sipariş oluşturulamadı: " . $stmt->error]);
}
?>
