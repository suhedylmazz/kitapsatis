<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id > 0) {
        global $mysqli;
        // Ürünü veritabanından çek
        $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
        if (!$stmt) {
            die("Prepare error: " . $mysqli->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();
        $stmt->close();

        if ($product) {
            $originalPrice = floatval($product['price']);
            $discountPercent = floatval($product['discount_percent']);

            if ($discountPercent > 0) {
                $priceToCart = $originalPrice * (1 - $discountPercent / 100);
            } else {
                $priceToCart = $originalPrice;
            }

            // Kategori bilgisini almak için
            $category = '';
            if (isset($product['category_id'])) {
                $stmtCat = $mysqli->prepare("SELECT category_name FROM categories WHERE id = ?");
                if ($stmtCat) {
                    $stmtCat->bind_param("i", $product['category_id']);
                    $stmtCat->execute();
                    $resCat = $stmtCat->get_result();
                    if ($cat = $resCat->fetch_assoc()) {
                        $category = $cat['category_name'];
                    }
                    $stmtCat->close();
                }
            }

            // Sepete ekleme işlemi: Ürün id'sini anahtar olarak kullanıyoruz
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            // Eğer ürün zaten sepetteyse, adet güncelle
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'product_id' => $product_id,
                    'name'       => $product['name'],
                    'price'      => $priceToCart,
                    'quantity'   => $quantity,
                    'category'   => $category
                ];
            }
        }
    }
}

header("Location: cart.php");
exit();

// Debug: Sepet içeriğini görmek için aşağıdaki kodu açabilirsiniz:
 echo '<pre>';
 print_r($_SESSION['cart']);
 echo '</pre>';
?>
