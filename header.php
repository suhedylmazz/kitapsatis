<?php
require_once 'config.php';
require_once 'functions.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sepet sayısını session tabanlı hesapla
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    $cartCount = count($_SESSION['cart']);
}

// Favori sayısını, kullanıcı giriş yapmışsa veritabanından al
$favCount = 0;
if (isLoggedIn()) {
    global $mysqli;
    $user_id = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT COUNT(*) as favCount FROM favorites WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($favCount);
    $stmt->fetch();
    $stmt->close();
}

// Ana kategorileri çek (parent_id IS NULL)
$mainCategories = [];
$queryMain = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name";
$resultMain = $mysqli->query($queryMain);
while ($row = $resultMain->fetch_assoc()) {
    $mainCategories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Online Kitap Mağazası</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
        integrity="sha512-pKpD/0QykBlsY3R2C5HPr6QXxSMmBG5p9e45PspQp0fL9XvdfK4xMxn/IV0qElPZ0+33v4m5R+6x1IxhO3v+FA==" 
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    /* Sepet/Favori sayacı için badge */
    .badge-count {
      position: relative;
      top: -8px;
      left: -5px;
      font-size: 0.75rem;
    }
    /* Header navigasyon menüsü */
    .navbar {
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar-brand {
      font-weight: bold;
      color: #5a8f7b;
    }
    .nav-link {
      color: #555 !important;
      transition: color 0.3s ease;
    }
    .nav-link:hover {
      color: #333 !important;
    }
    /* Dropdown menü */
    .navbar-nav .dropdown:hover > .dropdown-menu {
      display: block;
    }
    .dropdown-menu {
      margin-top: 0;
      border: none;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="index.php">LibHub</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" 
          aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  
  <div class="collapse navbar-collapse" id="navbarNavDropdown">
    <ul class="navbar-nav">
      <?php foreach ($mainCategories as $mainCat): ?>
          <?php 
          // Her ana kategori için alt kategorileri çekelim
          $mainId = $mainCat['id'];
          $querySub = "SELECT * FROM categories WHERE parent_id = $mainId ORDER BY category_name";
          $resultSub = $mysqli->query($querySub);
          ?>
          <?php if ($resultSub->num_rows > 0): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="category.php?cat=<?php echo $mainCat['id']; ?>" id="navbarDropdown<?php echo $mainCat['id']; ?>" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php echo htmlspecialchars($mainCat['category_name']); ?>
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown<?php echo $mainCat['id']; ?>">
                <?php while ($subCat = $resultSub->fetch_assoc()): ?>
                  <a class="dropdown-item" href="category.php?cat=<?php echo $subCat['id']; ?>">
                    <?php echo htmlspecialchars($subCat['category_name']); ?>
                  </a>
                <?php endwhile; ?>
              </div>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="category.php?cat=<?php echo $mainCat['id']; ?>">
                <?php echo htmlspecialchars($mainCat['category_name']); ?>
              </a>
            </li>
          <?php endif; ?>
      <?php endforeach; ?>
    </ul>
    
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <form class="form-inline" action="search.php" method="GET">
          <input class="form-control mr-sm-2" type="search" placeholder="Ara" name="q">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">
            <i class="fa fa-search"></i>
          </button>
        </form>
      </li>
      
      <?php if (isLoggedIn()): ?>
        <li class="nav-item">
          <a class="nav-link" href="cart.php">
            <i class="fa fa-shopping-cart"></i> Sepetim
            <span class="badge badge-danger badge-count"><?php echo $cartCount; ?></span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="favorites.php">
            <i class="fa fa-heart"></i> Favorilerim
            <span class="badge badge-danger badge-count"><?php echo $favCount; ?></span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">
            <i class="fa fa-sign-out-alt"></i> Çıkış
          </a>
        </li>
      <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="login.php">
            <i class="fa fa-sign-in-alt"></i> Giriş
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="register.php">
            <i class="fa fa-user-plus"></i> Üye Ol
          </a>
        </li>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['admin_id'])): ?>
        <li class="nav-item">
          <a class="nav-link" href="admin.php">
            <i class="fa fa-user-shield"></i> Admin Paneli
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
<!-- Header sonu. Diğer sayfalarda header.php include edildikten sonra içerik gelecek. -->
