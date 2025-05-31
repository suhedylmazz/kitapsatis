<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'functions.php';

// 1) Çizgi Roman kategorisi
$comicCategoryId = null;
$comicCatQuery = $mysqli->prepare("SELECT id FROM categories WHERE category_name = 'Çizgi Roman' LIMIT 1");
$comicCatQuery->execute();
$comicCatQuery->bind_result($comicCategoryId);
$comicCatQuery->fetch();
$comicCatQuery->close();

// Çizgi Roman ürünleri (Banner 1 için)
$comicBooks = [];
if ($comicCategoryId) {
    $stmt = $mysqli->prepare("SELECT id, name, image_url FROM products WHERE category_id = ? LIMIT 3");
    $stmt->bind_param("i", $comicCategoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $comicBooks[] = $row;
    }
    $stmt->close();
}

// 2) Tüm ürünlerden son 3 (Banner 2 için)
$bestsellersSql = "SELECT id, name, image_url FROM products ORDER BY id DESC";
$res = $mysqli->query($bestsellersSql);
$allBooks = [];
if ($res && $res->num_rows > 0) {
    $allBooks = $res->fetch_all(MYSQLI_ASSOC);
}
$last3 = array_slice($allBooks, -3);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <!-- Google Fonts: Pacifico -->
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<style>
  .navbar-brand {
    font-family: 'Pacifico', cursive !important;
    font-size: 4.5rem !important;
    color:rgb(19, 150, 75) !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3) !important;
  }
</style>

  <meta charset="UTF-8">
  <title>Online Kitap Mağazası</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <!-- Google Fonts: Poppins for genel içerik -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <!-- Google Fonts: Pacifico for LibHub (navbar-brand) -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
    /* Genel Stil Ayarları */
    body {
      background-color:rgb(231, 229, 229);
      font-family: 'Poppins', sans-serif;
      color: #333;
      margin: 0;
      padding: 0;
    }
    a {
      text-decoration: none;
    }
    /* Navbar */
    .navbar {
      box-shadow: 0 2px 4px rgba(106, 10, 10, 0.1);
      background-color:rgb(249, 249, 249);
      padding: 0.8rem 1rem;
    }
    .navbar-brand {
      /* Bu kısım, aşağıdaki eklenmiş stil ile değiştirilecek */
      font-weight: 500;
      color: #2c3e50;
      font-size: 1.7rem;
    }
    .nav-link {
      color: #2c3e50 !important;
      transition: color 0.3s ease;
      font-weight: 900;
      margin-right: 0.7rem;
    }
    .nav-link:hover {
      color:rgb(68, 205, 125)  !important;
    }
    /* Dropdown menü */
    .navbar-nav .dropdown:hover > .dropdown-menu {
      display: block;
    }
    .dropdown-menu {
      margin-top: 0;
      border: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    /* Carousel */
    .carousel-item {
      position: relative;
      text-align: center;
      padding: 100px 0;
      color: #fff;
      transition: background-color 0.5s ease-in-out;
    }
    /* Yeni Slide Arka Plan Renkleri */
    .carousel-item:nth-child(1) {
      background-color: #f8d7da;
    }
    .carousel-item:nth-child(2) {
      background-color: #d4edda;
    }
    .carousel-item:nth-child(3) {
      background-color:rgb(185, 221, 228);
    }
    .carousel-item h2,
    .carousel-item h4 {
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
      opacity: 0;
      animation: fadeInUp 1s forwards;
    }
    .carousel-item h2 {
      animation-delay: 0.3s;
    }
    .carousel-item h4 {
      animation-delay: 0.6s;
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .carousel-item img {
      height: 400px;
      object-fit: cover;
      width: 100%;
      border-radius: 8px;
    }
    /* Banner görselleri */
    .carousel-item > .container > div.d-flex img {
      margin: 0 5px;
      border-radius: 4px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }
    .carousel-item > .container > div.d-flex img:hover {
      transform: scale(1.05);
    }
    /* Kartlar (Favoriler, Çok Satanlar vb.) */
    .card {
      border: none;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      background-color: #fff;
      transition: transform 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card-img-top {
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
    }
    .card-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .card-text {
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 0.5rem;
    }
    /* Footer */
    footer {
      background-color:rgb(130, 126, 126);
      padding: 20px 0;
      border-top: 1px solid #ddd;
      font-size: 0.9rem;
      color: #777;
    }
    footer p, footer small {
      margin: 0;
    }
    /* Butonlar */
    .btn-primary {
      background-color:rgb(20, 147, 98);
      border: none;
      transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #507a68;
    }
    .btn-outline-danger {
      border-color: #e74c3c;
      color: #e74c3c;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    .btn-outline-danger:hover {
      background-color: #e74c3c;
      color: #fff;
    }
  </style>
</head>
<body>

<!-- Üst Menü (Navbar) -->
<nav class="navbar navbar-expand-lg navbar-light">
  <a class="navbar-brand" href="index.php">LibHub</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  
  <div class="collapse navbar-collapse" id="navbarNav">
    <!-- Soldaki Kategoriler (Dinamik) -->
    <ul class="navbar-nav">
      <?php
      // Ana kategorileri çek
      $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name";
      $mainCategories = $mysqli->query($sql);
      while ($main = $mainCategories->fetch_assoc()):
      ?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" 
           href="category.php?cat=<?php echo $main['id']; ?>" 
           id="navbarDropdown<?php echo $main['id']; ?>" 
           role="button" 
           data-toggle="dropdown">
          <?php echo htmlspecialchars($main['category_name']); ?>
        </a>
        <?php
        // Alt kategorileri çek
        $subSql = "SELECT * FROM categories WHERE parent_id = " . $main['id'] . " ORDER BY category_name";
        $subCategories = $mysqli->query($subSql);
        if($subCategories && $subCategories->num_rows > 0):
        ?>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown<?php echo $main['id']; ?>">
          <?php while ($sub = $subCategories->fetch_assoc()): ?>
          <a class="dropdown-item" href="category.php?cat=<?php echo $sub['id']; ?>">
            <?php echo htmlspecialchars($sub['category_name']); ?>
          </a>
          <?php endwhile; ?>
        </div>
        <?php endif; ?>
      </li>
      <?php endwhile; ?>
    </ul>
    
    <!-- Sağ Taraf -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <form class="form-inline" action="search.php" method="GET">
          <input class="form-control mr-sm-2" type="search" placeholder="Ara" name="q">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Ara</button>
        </form>
      </li>
      
      <?php if(isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
          <a class="nav-link" href="cart.php">
            <i class="fas fa-shopping-cart"></i> Sepetim
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="favorites.php">
            <i class="fas fa-heart"></i> Favorilerim
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Çıkış
          </a>
        </li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="login.php">Giriş</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Üye Ol</a></li>
      <?php endif; ?>
      
      <?php if(isset($_SESSION['admin_id'])): ?>
        <li class="nav-item"><a class="nav-link" href="admin.php">Admin Paneli</a></li> 
      <?php endif; ?>
    </ul>
  </div>
</nav>

<!-- Banner / Carousel -->
<div class="container-fluid p-0">
  <div id="mainCarousel" class="carousel slide" data-ride="carousel" data-interval="10000">
    <ol class="carousel-indicators">
      <li data-target="#mainCarousel" data-slide-to="0" class="active"></li>
      <li data-target="#mainCarousel" data-slide-to="1"></li>
      <li data-target="#mainCarousel" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner">
      <!-- Slide 1: Çizgi Roman Kampanyası -->
      <div class="carousel-item active">
        <div class="container d-flex align-items-center justify-content-between">
          <div>
            <h2 class="text-danger font-weight-bold">Çizgi Roman Kategorisine Özel</h2>
            <h4 class="text-dark mb-3">Sepette %50 İndirim!</h4>
            <a href="category.php?cat=<?php echo $comicCategoryId; ?>" class="btn btn-primary">Alışverişe Başla</a>
          </div>
          <div class="d-flex">
            <?php foreach ($comicBooks as $book): ?>
              <img src="<?php echo htmlspecialchars($book['image_url'] ?? 'images/default.jpg'); ?>"
                   alt="<?php echo htmlspecialchars($book['name']); ?>"
                   style="width: 160px; height: 270px; object-fit: cover;">
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <!-- Slide 2: Kaçırılmayacak Fiyatlar (Son 3 kitap) -->
      <?php
      // Sadece bu 3 kitabı veritabanından çekiyoruz
      $my3Sql = "
        SELECT id, name, image_url
        FROM products
        WHERE name IN ('Prenses Boyama Kitabı', 'Koparmalı Boyama - 2', 'Taşıtlar Boyama Kitabı')
      ";
      $res = $mysqli->query($my3Sql);
      $my3 = [];
      if ($res && $res->num_rows > 0) {
          $my3 = $res->fetch_all(MYSQLI_ASSOC);
      }
      ?>
      <div class="carousel-item">
        <div class="container d-flex align-items-center justify-content-between">
          <!-- Sol Tarafta Metin -->
          <div>
            <h4 class="text-dark mb-3">Boyama Kitaplarında</h4>
            <h2 class="text-success" style="font-weight: lighter;">2 Al 1 Öde Fırsatı!</h2>
            <a href="category.php?cat=33" class="btn btn-primary">Alışverişe Başla</a>
          </div>
          <!-- Sağ Tarafta Üç Kitap Görseli -->
          <div class="d-flex">
            <?php foreach ($my3 as $b): 
              $img = !empty($b['image_url']) ? $b['image_url'] : 'images/default.jpg';
            ?>
              <img src="<?php echo htmlspecialchars($img); ?>"
                   alt="<?php echo htmlspecialchars($b['name']); ?>"
                   style="width: 180px; height: 270px; object-fit: cover; margin-left:10px;">
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <!-- Slide 3: İNGİLİZCE Kitaplar Kampanyası -->
      <?php
      // İngilizce Kitaplar kategorisinin ID'sini alalım ve fallback değeri belirleyelim
      $englishCategoryId = null;
      $englishName = "İngilizce";
      $englishQuery = $mysqli->prepare("SELECT id FROM categories WHERE category_name = ? LIMIT 1");
      $englishQuery->bind_param("s", $englishName);
      $englishQuery->execute();
      $englishQuery->bind_result($englishCategoryId);
      $englishQuery->fetch();
      $englishQuery->close();
      if (!$englishCategoryId) {
          $englishCategoryId = 3;
      }
      $englishProducts = [];
      $stmtEnglish = $mysqli->prepare("SELECT id, name, image_url FROM products WHERE category_id = ? LIMIT 3");
      $stmtEnglish->bind_param("i", $englishCategoryId);
      $stmtEnglish->execute();
      $resultEnglish = $stmtEnglish->get_result();
      while ($row = $resultEnglish->fetch_assoc()) {
          $englishProducts[] = $row;
      }
      $stmtEnglish->close();
      ?>
      <div class="carousel-item">
        <div class="container d-flex align-items-center justify-content-between">
          <!-- Sol Kolon: Kampanya Metni -->
          <div>
            <h2 class="text-primary font-weight-bold">İngilizce Kitaplarda Sabit Fiyat Fırsatı!</h2>
            <h4 class="text-dark mb-3">Sadece 200 TL &amp; 300 TL Fiyatlarıyla</h4>
            <a href="category.php?cat=<?php echo $englishCategoryId; ?>" class="btn btn-primary">Alışverişe Başla</a>
          </div>
          <!-- Sağ Kolon: Ürün Görselleri veya Placeholder -->
          <div class="d-flex">
            <?php if (!empty($englishProducts)): ?>
              <?php foreach ($englishProducts as $prod): 
                $img = !empty($prod['image_url']) ? $prod['image_url'] : 'images/default.jpg';
              ?>
                <div style="flex:1; padding:5px;">
                  <img src="<?php echo htmlspecialchars($img); ?>" 
                       alt="<?php echo htmlspecialchars($prod['name']); ?>" 
                       style="width:100%; height:270px; object-fit:cover;">
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="flex:1; padding:5px; text-align:center;">
                <img src="https://placehold.co/160x250?text=200+TL" alt="200 TL" style="width:100%; height:150px; object-fit:cover;">
                <p class="mt-2">200 TL</p>
              </div>
              <div style="flex:1; padding:5px; text-align:center;">
                <img src="https://placehold.co/160x250?text=300+TL" alt="300 TL" style="width:100%; height:150px; object-fit:cover;">
                <p class="mt-2">300 TL</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <!-- Carousel Navigation -->
    <a class="carousel-control-prev" href="#mainCarousel" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </a>
    <a class="carousel-control-next" href="#mainCarousel" role="button" data-slide="next">
      <span class="carousel-control-next-icon"></span>
    </a>
  </div>
</div>

<!-- BU AYIN FAVORİ KITAPLARI -->
<?php
$favoriteNames = array_map('trim', array(
    'Mathepedia', 
    'Gölge', 
    'Amore Proibito - Aşk-ı Memnu', 
    'Hayalini Yorganına Göre Uzat - Başarıyla Zehirlenmiş Bir Toplumda Duymanız Gerekenler', 
    'Jeder Schritt zu dir', 
    'Uygarlığın Kökeni Sumerliler - 1', 
    'Brief an der Vater', 
    'Köşedeki Yaşlı Adam'
));
$placeholders = implode(',', array_fill(0, count($favoriteNames), '?'));
$stmtFav = $mysqli->prepare("SELECT id, name, author, price, image_url FROM products WHERE name IN ($placeholders)");
$types = str_repeat('s', count($favoriteNames));
$stmtFav->bind_param($types, ...$favoriteNames);
$stmtFav->execute();
$resultFav = $stmtFav->get_result();
?>
<div class="container mt-5">
  <h2>Bu Ayın Favori Kitapları</h2>
  <div class="row mt-3">
    <?php if ($resultFav && $resultFav->num_rows > 0): ?>
      <?php while ($prod = $resultFav->fetch_assoc()): ?>
        <div class="col-md-3">
          <div class="card h-100">
            <img src="<?php echo htmlspecialchars($prod['image_url'] ?: 'images/default.jpg'); ?>" 
                 class="card-img-top" 
                 alt="<?php echo htmlspecialchars($prod['name']); ?>">
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h5>
              <p class="card-text">Yazar: <?php echo htmlspecialchars($prod['author']); ?></p>
              <p class="card-text"><?php echo $prod['price']; ?> TL</p>
              <a href="product_detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary">Detay</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Bu ayın favori kitapları bulunamadı.</p>
    <?php endif; ?>
  </div>
</div>

<!-- ÇOK SATANLAR (Veritabanından Dinamik) -->
<div class="container mt-5">
  <h2>Çok Satanlar</h2>
  <div class="row mt-3">
    <?php
    $bestsellerSql = "SELECT id, name, author, price, image_url FROM products ORDER BY sold_count DESC LIMIT 8";
    $bestResult = $mysqli->query($bestsellerSql);
    if ($bestResult && $bestResult->num_rows > 0):
      while($prod = $bestResult->fetch_assoc()):
        $img = !empty($prod['image_url']) ? $prod['image_url'] : 'images/default.jpg';
    ?>
    <div class="col-md-3">
      <div class="card h-100">
        <img src="<?php echo htmlspecialchars($img); ?>" 
             class="card-img-top" 
             alt="Kitap Resmi" 
             style="width:100%; height:auto; object-fit:contain;">
        <div class="card-body">
          <h5 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h5>
          <p class="card-text"><?php echo htmlspecialchars($prod['author']); ?></p>
          <p class="card-text"><?php echo $prod['price']; ?> TL</p>
          <a href="product_detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary">Detay</a>
        </div>
      </div>
    </div>
    <?php endwhile; else: ?>
      <p>Çok satan ürün bulunamadı.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Footer -->
<footer class="bg-light text-center text-muted mt-5 py-3">
  <div class="container">
    <p>&copy; 2025 Kitap Mağazası</p>
    <small>Adres, iletişim bilgileri, sosyal medya linkleri vb.</small>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
