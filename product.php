<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    die("Ürün belirtilmedi.");
}
$product_id = intval($_GET['id']);
if ($product_id <= 0) {
    die("Geçersiz ürün ID.");
}

// Ortalama puan, toplam oy hesaplama
$ratingSql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_votes FROM product_ratings WHERE product_id = ?";
$stmtRating = $mysqli->prepare($ratingSql);
$stmtRating->bind_param("i", $product_id);
$stmtRating->execute();
$res = $stmtRating->get_result();
$ratingData = $res->fetch_assoc();
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$totalVotes = $ratingData['total_votes'];
$stmtRating->close();

// Kullanıcının verdiği oy (varsa)
$userVote = 0;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $userSql = "SELECT rating FROM product_ratings WHERE product_id=? AND user_id=?";
    $stmtUser = $mysqli->prepare($userSql);
    $stmtUser->bind_param("ii", $product_id, $user_id);
    $stmtUser->execute();
    $userRes = $stmtUser->get_result();
    if ($rowUser = $userRes->fetch_assoc()) {
        $userVote = $rowUser['rating'];
    }
    $stmtUser->close();
}

// Ürün bilgilerini çekelim
$sql = "
    SELECT 
        p.*,
        c.category_name AS child_name,
        parent.category_name AS parent_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN categories parent ON c.parent_id = parent.id
    WHERE p.id = ?
";
$stmtProduct = $mysqli->prepare($sql);
$stmtProduct->bind_param("i", $product_id);
$stmtProduct->execute();
$result = $stmtProduct->get_result();
$product = $result->fetch_assoc();
if (!$product) {
    die("Ürün bulunamadı.");
}
$stmtProduct->close();

// Fiyat ve indirim hesaplaması
$originalPrice = floatval($product['price']);
$discountPercent = floatval($product['discount_percent']);
if ($discountPercent > 0) {
    $discountedPrice = $originalPrice * (1 - $discountPercent / 100);
} else {
    $discountedPrice = $originalPrice;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> - Detay</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css">
    <!-- Font Awesome CSS (Integrity kaldırıldı) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Google Fonts: Pacifico for navbar-brand -->
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        .navbar-brand {
            font-family: 'Pacifico', cursive !important;
            font-size: 3.5rem !important;
            color: rgb(19, 150, 75) !important;
            text-shadow: 3px 2px 4px rgba(0,0,0,0.3) !important;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 40px;
        }
        /* Ürün Detay Kartı */
        .product-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 40px;
        }
        .product-image {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 5px;
            width: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .product-image:hover {
            transform: scale(1.02);
        }
        .product-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .product-author {
            font-size: 1.25rem;
            color: #555;
            margin-bottom: 20px;
        }
        /* Yıldızlı Oylama: Artan sıra (1'den 5'e) */
        .rating-stars {
            font-size: 24px;
            color: #ccc;
        }
        .rating-stars .star {
            cursor: pointer;
            margin-right: 5px;
            transition: color 0.2s;
        }
        .rating-stars .star:hover,
        .rating-stars .star:hover ~ .star {
            color: #ffd700;
        }
        .rating-stars .star.selected {
            color: #ffd700;
        }
        /* Fiyat */
        .price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #5a8f7b;
        }
        .old-price {
            font-size: 1.4rem;
            color: #999;
        }
        .discount-price {
            background-color: #ffc107;
            padding: 5px 10px;
            font-weight: 700;
            color: #000;
            border-radius: 4px;
            font-size: 1.4rem;
            margin-top: 5px;
        }
        .product-description {
            font-size: 1rem;
            line-height: 1.6;
            margin-top: 20px;
        }
        .list-group-item {
            border: none;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
            padding: 0.5rem 0;
        }
        .action-buttons .btn {
            margin-right: 10px;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }
        .action-buttons .btn:hover {
            transform: translateY(-2px);
        }
        /* Yorum Bölümü */
        .comment-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px 30px;
            margin-bottom: 40px;
            min-height: 500px;
            clear: both;
            width: 100%;
            display: block;
        }
        .comment-section h3 {
            border-bottom: 2px solid #5a8f7b;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            color: #333;
        }
        .comment-box {
            padding: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }
        .comment-box:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .comment-box strong {
            color: #5a8f7b;
            font-size: 1rem;
        }
        .comment-box span {
            font-size: 0.95rem;
            display: block;
            margin: 5px 0;
        }
        .comment-box small {
            color: #777;
            font-size: 0.85rem;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .product-title {
                font-size: 2rem;
            }
            .price, .old-price, .discount-price {
                font-size: 1.6rem;
            }
            .btn-primary, .btn-success, .btn-outline-danger {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
        }
  </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
  <!-- Ürün Detay Kartı -->
  <div class="row">
    <div class="col-md-12">
      <div class="product-card">
        <div class="row">
          <!-- Ürün Resmi -->
          <div class="col-md-4">
            <a href="<?php echo htmlspecialchars($product['image_url'] ?? 'images/default.jpg'); ?>" data-fancybox="gallery">
              <img id="zoom_01" src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/default.jpg'); ?>" 
                   data-zoom-image="<?php echo htmlspecialchars($product['image_url'] ?? 'images/default.jpg'); ?>" 
                   alt="<?php echo htmlspecialchars($product['name'] ?? 'Ürün Adı'); ?>" class="img-fluid product-image">
            </a>
          </div>
          <!-- Ürün Bilgileri -->
          <div class="col-md-8">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name'] ?? 'Ürün Adı'); ?></h1>
            <h4 class="product-author">Yazar: <?php echo htmlspecialchars($product['author'] ?? ''); ?></h4>
            
            <!-- Yıldızlı Oylama -->
            <div class="rating-section mb-3">
              <h5>Ortalama Puan: <?php echo $avgRating; ?>/5 (<?php echo $totalVotes; ?> oy)</h5>
              <p>
                <?php if($userVote > 0): ?>
                  Sizin oyunuz: <?php echo $userVote; ?> yıldız
                <?php else: ?>
                  Henüz oy vermediniz.
                <?php endif; ?>
              </p>
              <?php if(isLoggedIn()): ?>
              <div class="rating-stars mb-3">
                <!-- Yıldızları artan sıra ile (1'den 5'e) gösteriyoruz -->
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
              </div>
              <div id="rating-result" class="mb-3"></div>
              <?php endif; ?>
            </div>
            
            <!-- Fiyat Gösterimi -->
            <div class="price-section mb-3">
              <?php if ($discountPercent > 0): ?>
                <p class="old-price"><del><?php echo number_format($originalPrice, 2, ',', '.'); ?> TL</del></p>
                <div class="discount-price">
                  Sepette <?php echo number_format($discountedPrice, 2, ',', '.'); ?> TL
                </div>
              <?php else: ?>
                <p class="price"><?php echo number_format($originalPrice, 2, ',', '.'); ?> TL</p>
              <?php endif; ?>
            </div>
            
            <!-- Ürün Detay Listesi -->
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Dil:</strong> <?php echo htmlspecialchars($product['language']); ?></li>
              <li class="list-group-item"><strong>Baskı Yılı:</strong> <?php echo (int)$product['publication_year']; ?></li>
              <li class="list-group-item"><strong>Sayfa Sayısı:</strong> <?php echo (int)$product['page_count']; ?></li>
              <li class="list-group-item"><strong>Yayınevi:</strong> <?php echo htmlspecialchars($product['publisher']); ?></li>
            </ul>
            
            <!-- Ürün Açıklaması -->
            <p class="product-description">
              <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>
            
            <!-- İşlem Butonları -->
            <div class="action-buttons">
              <form action="add_favorite.php" method="post" style="display:inline-block;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="btn btn-outline-danger">
                  <i class="fa fa-heart"></i> Favorilere Ekle
                </button>
              </form>
              <form action="add_to_cart.php" method="post" style="display:inline-block;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="form-group" style="max-width: 120px; display:inline-block;">
                  <label for="quantity" style="font-size: 0.9rem;">Adet:</label>
                  <input type="number" name="quantity" id="quantity" value="1" min="1" max="10" class="form-control">
                </div>
                <button type="submit" name="add_to_cart" class="btn btn-success">Sepete Ekle</button>
              </form>
            </div>
          </div><!-- Ürün Bilgileri Sonu -->
        </div><!-- Row Sonu -->
      </div><!-- product-card Sonu -->
    </div><!-- col-md-12 Sonu -->
  </div><!-- Ürün Detay Row Sonu -->
  
  <!-- Yorumlar Bölümü (Alt) -->
  <div class="row">
    <div class="col-md-12">
      <div class="comment-section">
        <h3>Yorumlar</h3>
        <?php
        $stmtC = $mysqli->prepare("
          SELECT c.id AS comment_id, c.comment_text, c.user_id, c.created_at, u.username
          FROM comments c
          JOIN users u ON c.user_id = u.id
          WHERE c.product_id = ?
          ORDER BY c.created_at DESC
        ");
        $stmtC->bind_param("i", $product_id);
        $stmtC->execute();
        $resC = $stmtC->get_result();
        if ($resC->num_rows > 0) {
            while ($comment = $resC->fetch_assoc()) {
                echo '<div class="comment-box">';
                echo '<strong>' . htmlspecialchars($comment['username']) . ':</strong> ';
                echo '<span>' . htmlspecialchars($comment['comment_text']) . '</span>';
                echo '<br><small>' . $comment['created_at'] . '</small>';
                // Sil butonunu sadece yorum sahibi veya admin için göster
                if (isLoggedIn() && ($_SESSION['user_id'] == $comment['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'))) {
                    echo ' <a href="delete_comment.php?comment_id=' . $comment['comment_id'] . '&product_id=' . $product_id . '" class="btn btn-sm btn-danger delete-comment">Sil</a>';
                }
                echo '</div>';
            }
        } else {
            echo '<p>Henüz yorum yapılmamış.</p>';
        }
        $stmtC->close();
        ?>
        
        <!-- Yorum Ekle Formu -->
        <?php if(isLoggedIn()): ?>
        <form method="post" action="add_comment.php" class="mt-3">
          <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
          <div class="form-group">
            <label for="comment_text">Yorumunuz:</label>
            <textarea name="comment_text" id="comment_text" class="form-control" placeholder="Yorumunuz..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Yorum Gönder</button>
        </form>
        <?php else: ?>
        <p>Yorum yapmak için <a href="login.php">giriş yapınız</a>.</p>
        <?php endif; ?>
      </div><!-- comment-section Sonu -->
    </div><!-- col-md-12 Sonu -->
  </div><!-- Row Sonu -->
</div><!-- Container Sonu -->

<?php include 'footer.php'; ?>

<!-- JS Kütüphaneleri -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<!-- ElevateZoom kaldırıldı, böylece hata oluşmayacak -->
<script>
$(document).ready(function(){
  // (ElevateZoom entegrasyonu kaldırıldı)
  
  // SweetAlert2 ile yorum silme onayı
  $(document).on('click', '.delete-comment', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    Swal.fire({
      title: 'Yorumunuzu silmek istediğinize emin misiniz?',
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
  
  // Yıldızlı oylama (jQuery AJAX)
  $('.star').on('click', function(){
    var ratingValue = $(this).data('value');
    var productId   = <?php echo (int)$product_id; ?>;
    $.ajax({
      url: 'rate_product.php',
      type: 'POST',
      data: { rating: ratingValue, product_id: productId },
      dataType: 'json',
      success: function(response) {
        if(response && response.avgRating !== undefined && response.totalVotes !== undefined) {
          $('#rating-result').text("Ortalama Puan: " + response.avgRating + "/5 (" + response.totalVotes + " oy)");
          $('.star').each(function(){
            if($(this).data('value') <= ratingValue){
              $(this).addClass('selected');
            } else {
              $(this).removeClass('selected');
            }
          });
        } else {
          $('#rating-result').text("Bir hata oluştu.");
        }
      },
      error: function(xhr, status, error) {
        console.error("AJAX error:", error);
        $('#rating-result').text("Bir hata oluştu.");
      }
    });
  });
});
</script>
</body>
</html>
