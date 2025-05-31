<?php
session_start();
require_once 'functions.php'; // functions.php içerisinde config.php dahil ve global $mysqli tanımlı, isLoggedIn() fonksiyonu mevcut

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $user_id    = $_SESSION['user_id'];

    global $mysqli;  // Veritabanı bağlantısını elde ediyoruz

    // Aynı ürün favorilerde var mı kontrol edelim
    $checkSql = "SELECT id FROM favorites WHERE user_id = ? AND product_id = ?";
    $stmt = $mysqli->prepare($checkSql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Ürün favorilerde yoksa ekle
        $insertSql = "INSERT INTO favorites (user_id, product_id) VALUES (?, ?)";
        $stmt2 = $mysqli->prepare($insertSql);
        $stmt2->bind_param("ii", $user_id, $product_id);
        $stmt2->execute();
    }
    
    // Ürün detay sayfasına geri yönlendir (ya da favoriler sayfasına yönlendirebilirsiniz)
    header("Location: product.php?id=" . $product_id);
    exit;
} else {
    // Ürün ID gönderilmemişse anasayfaya yönlendir
    header("Location: index.php");
    exit;
}
?>
