<?php
session_start();
require_once 'functions.php'; // functions.php içerisinde config.php dahil ve global $mysqli tanımlı, getProduct() fonksiyonu mevcut

// Giriş kontrolü
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

global $mysqli;
$user_id = $_SESSION['user_id'];

// Favori ürünleri veritabanından çekelim
$favorites = array();
$sql = "SELECT product_id FROM favorites WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultFav = $stmt->get_result();
while ($row = $resultFav->fetch_assoc()) {
    $favorites[] = $row['product_id'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Favorilerim</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <!-- Google Fonts: Pacifico for LibHub (navbar-brand) -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
       .navbar-brand {
         font-family: 'Pacifico', cursive !important;
         font-size: 3.5rem !important;
         color: rgb(19, 150, 75) !important;
         text-shadow: 2px 2px 4px rgba(0,0,0,0.3) !important;
       }
       .card-img-top {
         width: 100%;
         height: auto;
         object-fit: cover;
       }
  </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <h1>Favorilerim</h1>
  <div class="row">
    <?php if (!empty($favorites)): ?>
      <?php foreach ($favorites as $fav_id): ?>
        <?php
        $product = getProduct($fav_id);
        if ($product):
            // Resim kontrolü
            $image = (isset($product['image_url']) && trim($product['image_url']) != '')
                ? trim($product['image_url'])
                : 'images/default.jpg';

            // Ortalama puan ve toplam oy
            $ratingSql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_votes
                          FROM product_ratings
                          WHERE product_id = ?";
            $stmtRating = $mysqli->prepare($ratingSql);
            $stmtRating->bind_param("i", $fav_id);
            $stmtRating->execute();
            $ratingRes = $stmtRating->get_result();
            $ratingRow = $ratingRes->fetch_assoc();

            $avgRating  = $ratingRow['avg_rating'] ? round($ratingRow['avg_rating'], 1) : 0;
            $totalVotes = $ratingRow['total_votes'];

            // Yıldızlar için tam sayı yuvarlama
            $rounded = round($avgRating);
            $starHtml = "";
            for ($i = 1; $i <= 5; $i++) {
                $starHtml .= ($i <= $rounded) ? '&#9733;' : '&#9734;';
            }
        ?>
          <div class="col-md-3">
            <div class="card mb-3">
              <img src="<?php echo htmlspecialchars($image); ?>" 
                   onerror="this.onerror=null; this.src='images/default.jpg';" 
                   class="card-img-top" 
                   alt="<?php echo htmlspecialchars($product['name']); ?>">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>

                <!-- Yıldızlı ortalama puan gösterimi -->
                <p style="color:#ffa500; font-size:18px; margin-bottom: 5px;">
                  <?php echo $starHtml; ?>
                  (<?php echo $avgRating; ?>/5)
                </p>
                
                <!-- Fiyat gösterimi -->
                <p class="card-text" style="margin-bottom: 10px;">
                  <?php echo number_format($product['price'], 2); ?> TL
                </p>

                <!-- Detay butonu -->
                <a href="remove_favorite.php?product_id=<?php echo $product['id']; ?>&redirect=product.php?id=<?php echo $product['id']; ?>" 
                   class="btn btn-primary">
                  Detay
                </a>
                <!-- Favoriden Sil butonu (SweetAlert2 onay ile) -->
                <a href="remove_favorite.php?product_id=<?php echo $product['id']; ?>" 
                   class="btn btn-danger btn-sm mt-2 delete-fav">
                   <i class="fa fa-trash"></i> Favoriden Sil
                </a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <p>Ürün bulunamadı.</p>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Favorilerinizde ürün bulunmamaktadır.</p>
    <?php endif; ?>
  </div>
</div>
<?php include 'footer.php'; ?>
<!-- jQuery ve Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
$(document).ready(function(){
  // Favorilerde silme onayı için SweetAlert2
  $(document).on('click', '.delete-fav', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    Swal.fire({
      title: 'Favoriyi silmek istediğinize emin misiniz?',
      text: "Bu işlem geri alınamaz!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#aaa',
      confirmButtonText: 'Evet, sil!',
      cancelButtonText: 'Vazgeç'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
});
</script>
</body>
</html>
