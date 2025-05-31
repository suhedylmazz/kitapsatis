<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// Arama parametresini alıyoruz
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q == '') {
    die("Lütfen arama yapmak için bir kelime girin.");
}

// Sayfa numarası (varsayılan 1)
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 16;              // Her sayfada 16 ürün
$offset = ($page - 1) * $limit;

// Arama için %wildcard% kullanıyoruz
$searchParam = "%" . $q . "%";

// Toplam sonuç sayısını bulmak için sorgu
$countSql = "SELECT COUNT(*) FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.name LIKE ? OR p.author LIKE ? OR c.category_name LIKE ?";
$stmtCount = $mysqli->prepare($countSql);
$stmtCount->bind_param("sss", $searchParam, $searchParam, $searchParam);
$stmtCount->execute();
$stmtCount->bind_result($totalResults);
$stmtCount->fetch();
$stmtCount->close();

// Toplam sayfa sayısını hesapla
$totalPages = ceil($totalResults / $limit);

// Arama sonuçlarını çekmek için sorgu
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.name LIKE ? OR p.author LIKE ? OR c.category_name LIKE ? 
        LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Arama Sonuçları - <?php echo htmlspecialchars($q); ?></title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
    .navbar-brand {
    font-family: 'Pacifico', cursive !important;
    font-size: 3.5rem !important;
    color:rgb(19, 150, 75) !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3) !important;
  }
    .product-card img {
      width: 100%;
      height: auto;
      object-fit: cover;
    }
    .pagination li.active a {
      background-color: #507a68;
      border-color: #507a68;
      color: white;
    }
  </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <h1>Arama Sonuçları: <?php echo htmlspecialchars($q); ?></h1>
  <?php if (count($products) == 0): ?>
    <p>Sonuç bulunamadı.</p>
  <?php else: ?>
    <div class="row">
      <?php foreach ($products as $product): ?>
        <div class="col-md-3">
          <div class="card mb-3 product-card">
            <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-img-top">
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
              <p class="card-text">Yazar: <?php echo htmlspecialchars($product['author']); ?></p>
              <p class="card-text">Kategori: <?php echo htmlspecialchars($product['category_name']); ?></p>
              <p class="card-text"><?php echo $product['price']; ?> TL</p>
              <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Detay</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Sayfalama (Pagination) Alanı -->
    <nav aria-label="Sayfa navigasyonu">
      <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="search.php?q=<?php echo urlencode($q); ?>&page=<?php echo $page - 1; ?>">Önceki</a>
          </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
            <a class="page-link" href="search.php?q=<?php echo urlencode($q); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
          <li class="page-item">
            <a class="page-link" href="search.php?q=<?php echo urlencode($q); ?>&page=<?php echo $page + 1; ?>">Sonraki</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
