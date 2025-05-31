<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config.php'; // Veritabanı bağlantısı

// Admin kontrolü: Sadece admin yetkisi olanlar ürünü güncelleyebilir.
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["error" => "Admin yetkisi gereklidir."]);
    exit;
}

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Ürün bilgilerini almak
    $product = getProduct($product_id);
    if (!$product) {
        echo json_encode(["error" => "Ürün bulunamadı."]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Formdan gelen değerler
        $name             = $_POST['name'];
        $description      = $_POST['description'];
        $price            = $_POST['price'];
        $discount_percent = $_POST['discount_percent'];
        $category         = trim($_POST['category']);
        $language         = $_POST['language'];
        $publication_year = $_POST['publication_year'];
        $page_count       = $_POST['page_count'];
        $image            = $_POST['image'];
        $author           = $_POST['author'];
        $publisher        = $_POST['publisher'];
        
        // Alt kategori sorgulaması
        $stmtCat = $conn->prepare("SELECT id FROM categories WHERE category_name = ? AND parent_id IS NOT NULL");
        if (!$stmtCat) {
            echo json_encode(["error" => "Kategori sorgu hatası: " . $conn->error]);
            exit;
        }
        $stmtCat->bind_param("s", $category);
        $stmtCat->execute();
        $stmtCat->bind_result($categoryId);
        if (!$stmtCat->fetch()) {
            echo json_encode(["error" => "Girilen kategori bulunamadı. Lütfen alt kategori giriniz."]);
            exit;
        }
        $stmtCat->close();
        
        // Ürün güncelleme işlemi
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, discount_percent = ?, category_id = ?, language = ?, publication_year = ?, page_count = ?, image_url = ?, author = ?, publisher = ? WHERE id = ?");
        if (!$stmt) {
            echo json_encode(["error" => "Ürün güncelleme sorgu hatası: " . $conn->error]);
            exit;
        }

        // Parametre tipi: 
        // name: s, description: s, price: d, discount_percent: d, category_id: i, language: s, publication_year: i, page_count: i, image_url: s, author: s, publisher: s, product_id: i
        $stmt->bind_param("ssddisiisssi", $name, $description, $price, $discount_percent, $categoryId, $language, $publication_year, $page_count, $image, $author, $publisher, $product_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Ürün başarıyla güncellendi.",
                "product_id" => $product_id
            ]);
        } else {
            echo json_encode(["error" => "Ürün güncellenemedi: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["error" => "Geçersiz istek yöntemi."]);
    }
} else {
    echo json_encode(["error" => "Ürün ID'si belirtilmedi."]);
}
?>
