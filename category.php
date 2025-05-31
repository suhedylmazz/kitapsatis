<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';
require_once 'functions.php';

// URL'den kategori id'sini alıyoruz
$cat_id = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
if ($cat_id == 0) {
    die("Kategori belirtilmedi.");
}

// Sayfalama ayarları
$limit = 16; // Her sayfada gösterilecek ürün sayısı
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

global $mysqli;
// Kategori bilgisini alalım
$stmtCategory = $mysqli->prepare("SELECT category_name FROM categories WHERE id = ?");
$stmtCategory->bind_param("i", $cat_id);
$stmtCategory->execute();
$stmtCategory->bind_result($categoryName);
if (!$stmtCategory->fetch()) {
    die("Kategori bulunamadı.");
}
$stmtCategory->close();

// Toplam ürün sayısını bulalım (sayfalama için)
$stmtCount = $mysqli->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
$stmtCount->bind_param("i", $cat_id);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$rowCount = $resultCount->fetch_assoc();
$totalProducts = $rowCount['total'];
$stmtCount->close();
$totalPages = ceil($totalProducts / $limit);

// Bu kategoriye ait ürünleri çekelim (limit ve offset kullanarak)
$stmtProducts = $mysqli->prepare("SELECT * FROM products WHERE category_id = ? LIMIT ? OFFSET ?");
$stmtProducts->bind_param("iii", $cat_id, $limit, $offset);
$stmtProducts->execute();
$resultProducts = $stmtProducts->get_result();
$products = [];
while ($row = $resultProducts->fetch_assoc()) {
    $products[] = $row;
}
$stmtProducts->close();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($categoryName); ?> - Ürünler</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Google Fonts: Pacifico for LibHub (navbar-brand) -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        
  .navbar-brand {
    font-family: 'Pacifico', cursive !important;
    font-size: 3.5rem !important;
    color:rgb(19, 150, 75) !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3) !important;
  }

      /* Genel Stil Ayarları */
      body {
         background-color: #f2f2f2;
         font-family: 'Poppins', sans-serif;
         color: #333;
         margin: 0;
         padding: 0;
      }
      a {
         text-decoration: none;
      }
      /* Başlık */
      h1 {
         margin-bottom: 1.5rem;
         font-weight: 600;
         color: #444;
         text-align: center;
         padding: 1rem 0;
         border-bottom: 2px solid #ddd;
      }
      /* Kart Stilleri */
      .product-card {
         border: none;
         border-radius: 8px;
         box-shadow: 0 2px 6px rgba(0,0,0,0.1);
         background-color: #fff;
         transition: transform 0.3s ease, box-shadow 0.3s ease;
         overflow: hidden;
         margin-bottom: 1.5rem;
      }
      .product-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      }
      .product-card img {
         width: 100%;
         height: auto;
         object-fit: contain;
         transition: transform 0.3s ease;
      }
      .product-card img:hover {
         transform: scale(1.05);
      }
      .product-card .card-body {
         padding: 1rem;
      }
      .card-title {
         font-size: 1.1rem;
         font-weight: 600;
         margin-bottom: 0.5rem;
         color: #333;
      }
      .card-text {
         font-size: 0.95rem;
         color: #555;
         margin-bottom: 0.75rem;
      }
      /* Buton Stilleri */
      .btn-primary {
          background-color: #5a8f7b !important;
          border-color: #5a8f7b !important;
          color: #fff;
      }
      .btn-primary:hover, .btn-primary:focus {
          background-color: #507a68 !important;
          border-color: #507a68 !important;
          color: #fff;
      }
      /* Ürün Listesi Düzeni: Bir satırda 4 ürün */
      .product-list {
          display: flex;
          flex-wrap: wrap;
          gap: 20px;
      }
      .product-item {
          /* Her satırda 4 ürün için yüzde 25 e yakın genişlik, margin'leri hesaba katıyoruz */
          width: calc(25% - 20px);
      }
      /* Responsive ayarlar */
      @media (max-width: 992px) {
          .product-item {
              width: calc(33.33% - 20px);
          }
      }
      @media (max-width: 768px) {
          .product-item {
              width: calc(50% - 20px);
          }
      }
      @media (max-width: 576px) {
          .product-item {
              width: 100%;
          }
      }
      /* Sayfalama Stilleri */
      .pagination {
          margin: 20px auto;
          display: flex;
          justify-content: center;
      }
      .pagination a {
          color: #5a8f7b;
          padding: 8px 16px;
          text-decoration: none;
          border: 1px solid #ddd;
          margin: 0 4px;
          border-radius: 5px;
          transition: background-color 0.3s;
      }
      .pagination a.active {
          background-color: #5a8f7b;
          color: #fff;
          border: 1px solid #5a8f7b;
      }
      .pagination a:hover:not(.active) {
          background-color: #ddd;
      }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h1><?php echo htmlspecialchars($categoryName); ?> Kategorisi</h1>
    
    <?php if (empty($products)): ?>
        <p class="text-center">Bu kategoride ürün bulunmamaktadır.</p>
    <?php else: ?>
        <div class="row product-list">
            <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <div class="card product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <?php
                            $originalPrice = floatval($product['price']);
                            // Örneğin, "çizgi roman" için indirim uygulanıyor (bu kısmı ihtiyaca göre düzenleyebilirsiniz)
                            if (strtolower(trim($categoryName)) == 'çizgi roman') {
                                $discountedPrice = $originalPrice * 0.5;
                                ?>
                                <p class="card-text">
                                    Fiyat: <del><?php echo number_format($originalPrice, 2, ',', '.'); ?> TL</del><br>
                                    Sepette %50 indirimli hali: <strong><?php echo number_format($discountedPrice, 2, ',', '.'); ?> TL</strong>
                                </p>
                                <?php
                            } else {
                                ?>
                                <p class="card-text"><?php echo number_format($originalPrice, 2, ',', '.'); ?> TL</p>
                                <?php
                            }
                            ?>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Detaylar</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sayfalama -->
        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="?cat=<?php echo $cat_id; ?>&page=<?php echo $page - 1; ?>">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): 
                    $active = ($i == $page) ? "active" : "";
            ?>
                <a class="<?php echo $active; ?>" href="?cat=<?php echo $cat_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?cat=<?php echo $cat_id; ?>&page=<?php echo $page + 1; ?>">&raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
<!-- jQuery ve Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
