<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';
require_once 'functions.php';

// Kullanıcı giriş kontrolü: Giriş yapılmamışsa login.php'ye yönlendir.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ödeme işlemi form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cancel'])) {
        // İptal edildiğinde sepeti temizleyip kullanıcıyı cart.php'ye yönlendirebilirsiniz.
        echo "Ödeme iptal edildi.";
        // İsteğe bağlı: unset($_SESSION['cart']);
        exit;
    } elseif (isset($_POST['confirm'])) {
        $user_id = $_SESSION['user_id'];
        $payment_method = $_POST['payment_method']; // Havale, Kapıda veya Kredi Kartı
        // Sipariş oluştur: orders tablosuna ekle
        $stmt = $mysqli->prepare("INSERT INTO orders (user_id, order_date, payment_method, order_status) VALUES (?, NOW(), ?, 'Completed')");
        if (!$stmt) {
            die("Order prepare error: " . $mysqli->error);
        }
        $stmt->bind_param("is", $user_id, $payment_method);
        if (!$stmt->execute()) {
            die("Order execute error: " . $stmt->error);
        }
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Sepetteki ürünleri order_details tablosuna ekle
        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                // Ürün fiyatını alalım (isteğe bağlı: toplam hesaplaması için)
                $stmtProd = $mysqli->prepare("SELECT price FROM products WHERE id = ?");
                $stmtProd->bind_param("i", $product_id);
                $stmtProd->execute();
                $stmtProd->bind_result($price);
                $stmtProd->fetch();
                $stmtProd->close();

                // Sipariş detaylarını ekleyelim
                $stmtDetail = $mysqli->prepare("INSERT INTO order_details (order_id, product_id, quantity) VALUES (?, ?, ?)");
                if (!$stmtDetail) {
                    die("Order detail prepare error: " . $mysqli->error);
                }
                $stmtDetail->bind_param("iii", $order_id, $product_id, $quantity);
                if (!$stmtDetail->execute()) {
                    die("Order detail execute error: " . $stmtDetail->error);
                }
                $stmtDetail->close();
            }
        }
        // Ödeme başarılı, sepeti temizleyin
        unset($_SESSION['cart']);
        echo "Ödeme başarılı! Siparişiniz oluşturuldu.";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ödeme Yap</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h1>Ödeme Yap</h1>
    <form method="post" action="payment.php">
        <div class="form-group">
            <label for="payment_method">Ödeme Yöntemi</label>
            <select name="payment_method" id="payment_method" class="form-control">
                <option value="Havale">Havale</option>
                <option value="Kapıda">Kapıda</option>
                <option value="Kredi Kartı">Kredi Kartı</option>
            </select>
        </div>
        <button type="submit" name="confirm" class="btn btn-success">Ödeme Yap</button>
        <button type="submit" name="cancel" class="btn btn-danger">İptal Et</button>
    </form>
</div>
<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
