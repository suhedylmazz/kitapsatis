<?php
session_start();
// Ürün ID'si URL'den alınır
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    // Eğer ürün sepette varsa kaldırılır
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}
// İşlem tamamlandıktan sonra sepet sayfasına yönlendir
header("Location: cart.php");
exit;
?>
