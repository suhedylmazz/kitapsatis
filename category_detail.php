<?php
session_start();
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0) {
  die("Geçersiz kategori ID.");
}

// Kategori bilgisi
$sql = "SELECT * FROM categories WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$category = $res->fetch_assoc();

if(!$category) {
  die("Kategori bulunamadı.");
}

// Bu kategoriye ait ürünler (örnek)
$prodSql = "SELECT * FROM products WHERE category_id = ?";
$stmt2 = $mysqli->prepare($prodSql);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$prodRes = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($category['category_name']); ?> - Kategori Detayı</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<h1>Kategori: <?php echo htmlspecialchars($category['category_name']); ?></h1>
<p><?php echo nl2br(htmlspecialchars($category['category_desc'])); ?></p>

<h2>Bu Kategorideki Ürünler</h2>
<div class="row">
  <?php while($prod = $prodRes->fetch_assoc()): ?>
    <div class="col-md-3">
      <div class="card mb-3">
        <img src="https://placehold.co/300x180?text=<?php echo urlencode($prod['product_name']); ?>"
             class="card-img-top" alt="Ürün Resmi">
        <div class="card-body">
          <h5 class="card-title"><?php echo htmlspecialchars($prod['product_name']); ?></h5>
          <p>Yazar: <?php echo htmlspecialchars($prod['author']); ?></p>
          <p>Fiyat: <?php echo $prod['price']; ?> TL</p>
          <a href="product_detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary">Detay</a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>
</body>
</html>
