// POST JSON: { "user_id": 1, "total_amount": 120.50 }
<?php
session_start();
require_once 'functions.php';
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(["error" => "Kullanıcı girişi yapılmamış."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$total = $_POST['total'];  // Siparişin toplam tutarı
$payment_method = $_POST['payment_method'];  // Ödeme yöntemi
$order_status = 'pending';  // Başlangıçta sipariş durumu 'pending' (beklemede)

if (empty($total) || empty($payment_method)) {
    echo json_encode(["error" => "Toplam tutar ve ödeme yöntemi gerekli."]);
    exit;
}

// Siparişi veritabanına ekleme
$sql = "INSERT INTO orders (user_id, total, payment_method, order_status, order_date) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idss", $user_id, $total, $payment_method, $order_status);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;  // Yeni eklenen siparişin ID'si
    echo json_encode(["success" => "Sipariş başarıyla oluşturuldu.", "order_id" => $order_id]);
} else {
    echo json_encode(["error" => "Sipariş oluşturulurken bir hata oluştu."]);
}

$stmt->close();
?>
