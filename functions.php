<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısını içeren dosyayı dahil ediyoruz.
require_once 'config.php';

// Kullanıcı girişi kontrolü
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Admin kontrolü
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Belirli kategorideki ürünleri getirir (MySQLi)
// Not: Eğer ürün tablonuzda kategori bilgisi "category_id" sütunu olarak saklanıyorsa, 
// sorguyu ona göre güncellemeniz gerekebilir.
function getProductsByCategory($category) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE category = ?");
    if (!$stmt) {
        die("Prepare error in getProductsByCategory: " . $mysqli->error);
    }
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = array();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

// Arama fonksiyonu (MySQLi)
function searchProducts($keyword) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    if (!$stmt) {
        die("Prepare error in searchProducts: " . $mysqli->error);
    }
    $likeKeyword = "%$keyword%";
    $stmt->bind_param("ss", $likeKeyword, $likeKeyword);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = array();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

// Tek bir ürünün detaylarını getirir (MySQLi)
function getProduct($product_id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
    if (!$stmt) {
        die("Prepare error in getProduct: " . $mysqli->error);
    }
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    return $product;
}

// Sepete ekleme (aynı ürün en fazla 10 adet eklenebilir)
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    // Önceki değeri göz ardı edip, yeni girilen adedi atıyoruz. Maksimum 10 olarak sınırlıyoruz.
    $_SESSION['cart'][$product_id] = min($quantity, 10);
}



// Sepetten ürün kaldırma
function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Sepetteki ürünleri getirir (MySQLi)
function getCartProducts() {
    global $mysqli;
    $cart = array();
    if (isset($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        if (count($ids) > 0) {
            $ids_string = implode(',', array_map('intval', $ids));
            $query = "SELECT * FROM products WHERE id IN ($ids_string)";
            $result = $mysqli->query($query);
            while ($row = $result->fetch_assoc()) {
                $cart[] = $row;
            }
        }
    }
    return $cart;
}

// Favorilere ekleme
function addToFavorites($product_id) {
    if (!isset($_SESSION['favorites'])) {
        $_SESSION['favorites'] = array();
    }
    if (!in_array($product_id, $_SESSION['favorites'])) {
        $_SESSION['favorites'][] = $product_id;
    }
}
if (!function_exists('getFavoriteProducts')) {
    function getFavoriteProducts() {
        global $mysqli;
        $favorites = array();
        if (isset($_SESSION['favorites']) && is_array($_SESSION['favorites']) && count($_SESSION['favorites']) > 0) {
            $ids = $_SESSION['favorites'];
            $ids_string = implode(',', array_map('intval', $ids));
            $query = "SELECT * FROM products WHERE id IN ($ids_string)";
            $result = $mysqli->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $favorites[] = $row;
                }
                $result->free();
            } else {
                die("Veritabanı hatası: " . $mysqli->error);
            }
        }
        return $favorites;
    }
}



// Favori ürünleri getirir (MySQLi)
function getFavoriteProducts() {
    global $mysqli;
    $favorites = array();
    if (isset($_SESSION['favorites']) && is_array($_SESSION['favorites']) && count($_SESSION['favorites']) > 0) {
        $ids = $_SESSION['favorites'];
        $ids_string = implode(',', array_map('intval', $ids));
        $query = "SELECT * FROM products WHERE id IN ($ids_string)";
        $result = $mysqli->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $favorites[] = $row;
            }
            $result->free();
        } else {
         die("Veritabanı hatası: " . $mysqli->error);
        }
    }
    return $favorites;
}
?>
