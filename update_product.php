<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'functions.php'; // functions.php içinde session_start(), config.php ve isAdmin() tanımlı olsun.

if (!isAdmin()) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Ürün ID'si belirtilmedi.");
}
$product_id = intval($_GET['id']);
$product = getProduct($product_id);
if (!$product) {
    die("Ürün bulunamadı.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    // Formdan gelen değerler
    $name             = $_POST['name'];
    $description      = $_POST['description'];
    $price            = $_POST['price'];
    $discount_percent = $_POST['discount_percent']; // Yeni eklenen alan
    $category         = trim($_POST['category']);
    $language         = $_POST['language'];
    $publication_year = $_POST['publication_year'];
    $page_count       = $_POST['page_count'];
    $image            = $_POST['image'];
    $author           = $_POST['author'];
    $publisher        = $_POST['publisher'];
    
    global $mysqli;
    // Alt kategori sorgulaması: Sadece alt kategori olması isteniyor.
    $stmtCat = $mysqli->prepare("SELECT id FROM categories WHERE category_name = ? AND parent_id IS NOT NULL");
    if (!$stmtCat) {
        die("Kategori sorgu hatası: " . $mysqli->error);
    }
    $stmtCat->bind_param("s", $category);
    $stmtCat->execute();
    $stmtCat->bind_result($categoryId);
    if (!$stmtCat->fetch()) {
        die("Girilen kategori bulunamadı. Lütfen alt kategori giriniz.");
    }
    $stmtCat->close();
    
    // Ürün güncelleme sorgusu – discount_percent alanını da ekliyoruz.
    $stmt = $mysqli->prepare("UPDATE products SET name = ?, description = ?, price = ?, discount_percent = ?, category_id = ?, language = ?, publication_year = ?, page_count = ?, image_url = ?, author = ?, publisher = ? WHERE id = ?");
    if (!$stmt) {
        die("Ürün güncelleme sorgu hatası: " . $mysqli->error);
    }
    // Parametre tipi: 
    // name: s, description: s, price: d, discount_percent: d, category_id: i, language: s, publication_year: i, page_count: i, image_url: s, author: s, publisher: s, product_id: i
    $stmt->bind_param("ssddisiisssi", $name, $description, $price, $discount_percent, $categoryId, $language, $publication_year, $page_count, $image, $author, $publisher, $product_id);
    
    if ($stmt->execute()) {
        $success = "Ürün başarıyla güncellendi.";
        $product = getProduct($product_id);
    } else {
        $error = "Ürün güncellenemedi: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Güncelle - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
 <!-- Google Fonts: Pacifico for LibHub (navbar-brand) -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  
  <style>
   .navbar-brand {
    font-family: 'Pacifico', cursive !important;
    font-size: 3.5rem !important;
    color:rgb(19, 150, 75) !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3) !important;
  }
   </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
    
    <h1>Ürün Güncelle</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="name">Ürün Adı</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Açıklama</label>
            <textarea name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Fiyat</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
        </div>
        <div class="form-group">
            <label for="discount_percent">İndirim Oranı (%)</label>
            <input type="number" step="0.01" name="discount_percent" class="form-control" value="<?php echo isset($product['discount_percent']) ? $product['discount_percent'] : '0'; ?>" required>
            <small>Örneğin, %50 indirim için 50, %0 indirim için 0 girin.</small>
        </div>
        <div class="form-group">
            <label for="category">Kategori Adı</label>
            <input type="text" name="category" class="form-control" value="<?php 
                // Kategori adını çekiyoruz
                $stmtCatName = $mysqli->prepare("SELECT category_name FROM categories WHERE id = ?");
                $stmtCatName->bind_param("i", $product['category_id']);
                $stmtCatName->execute();
                $stmtCatName->bind_result($catName);
                $stmtCatName->fetch();
                echo htmlspecialchars($catName);
                $stmtCatName->close();
            ?>" required>
            <small>Lütfen alt kategori adını girin (örn: Roman, Edebiyat, vb.).</small>
        </div>
        <div class="form-group">
            <label for="language">Dil</label>
            <input type="text" name="language" class="form-control" value="<?php echo htmlspecialchars($product['language']); ?>">
        </div>
        <div class="form-group">
            <label for="publication_year">Baskı Yılı</label>
            <input type="number" name="publication_year" class="form-control" value="<?php echo $product['publication_year']; ?>">
        </div>
        <div class="form-group">
            <label for="page_count">Sayfa Sayısı</label>
            <input type="number" name="page_count" class="form-control" value="<?php echo $product['page_count']; ?>">
        </div>
        <div class="form-group">
            <label for="image">Resim URL</label>
            <input type="text" name="image" class="form-control" value="<?php echo htmlspecialchars($product['image_url']); ?>">
        </div>
        <div class="form-group">
            <label for="author">Yazar</label>
            <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($product['author']); ?>">
        </div>
        <div class="form-group">
            <label for="publisher">Yayınevi</label>
            <input type="text" name="publisher" class="form-control" value="<?php echo htmlspecialchars($product['publisher']); ?>">
        </div>
        <button type="submit" name="update_product" class="btn btn-primary">Güncelle</button>
    </form>
</div>
<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
