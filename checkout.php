<?php
session_start();
require_once 'config.php';    // Veritabanı bağlantısı ($conn) burada tanımlı
require_once 'functions.php'; // getProduct(), isLoggedIn() gibi fonksiyonlar

// Kullanıcı giriş yapmamışsa yönlendir
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Sepet boşsa işlem yapma
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Sepetiniz boş.");
}

$user_id = $_SESSION['user_id'];

// Ödeme yöntemini formdan alıyoruz (örneğin 'Kredi Kartı', 'Banka Havalesi' vs.)
// Eğer form gönderilmemişse varsayılan olarak 'Kredi Kartı' kullanıyoruz.
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Kredi Kartı';

// Siparişin orders tablosuna eklenmesi
// Orders tablonuzda: id, user_id, order_date, payment_method, order_status sütunları bulunuyor.
// order_status sütunu için varsayılan değeri 'Pending' (beklemede) olarak ayarladığınızdan, burada sadece user_id ve payment_method ekliyoruz.
$sql = "INSERT INTO orders (user_id, payment_method) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Hata: " . $conn->error);
}
$stmt->bind_param("is", $user_id, $payment_method);

if ($stmt->execute()) {
    // Sipariş başarıyla oluşturulduktan sonra oluşturulan siparişin id'sini alıyoruz
    $order_id = $stmt->insert_id;
    
    // İsteğe bağlı: Eğer sipariş detaylarını saklamak için order_items tablonuz varsa,
    // burada sepetinizdeki ürünleri de kaydedebilirsiniz.
    /*
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProduct($product_id);
        if($product) {
            $price = $product['price'];
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_item->execute();
        }
    }
    */
    
    // Sipariş oluşturulduktan sonra sepet temizleniyor
    unset($_SESSION['cart']);
    
    // Sipariş onay sayfasına yönlendiriyoruz
    header("Location: order_confirmation.php?order_id=" . $order_id);
    exit;
} else {
    die("Sipariş oluşturulamadı: " . $stmt->error);
}
?>
