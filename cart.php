<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';
require_once 'functions.php';

/**
 * Kampanyalı ürünler için toplam ücreti hesaplar.
 * Kampanya: "2 al 1 öde" (örneğin, Boyama Kitapları)
 * Her 2 ürün grubundan yalnızca en yüksek fiyatlı ürünün ücreti alınır.
 * Eğer fiyatlar eşitse, ilk eklenenin fiyatı dikkate alınır.
 *
 * @param array $items Kampanyaya dahil ürünler, her biri ['price' => ..., 'quantity' => ...]
 * @return float Kampanyalı ürünlerin toplam ödenecek tutarı
 */
function calculateCampaignTotal($items) {
    $expanded = [];
    foreach ($items as $item) {
        // Her ürünü, quantity değeri kadar ayrı ayrı listeye ekliyoruz
        for ($i = 0; $i < $item['quantity']; $i++) {
            $expanded[] = $item;
        }
    }
    // Fiyatlara göre azalan şekilde sırala (yüksek fiyat ilk sırada)
    usort($expanded, function($a, $b) {
        return $b['price'] - $a['price'];
    });
    $total = 0;
    // Her iki üründen yalnızca ilk ürünün fiyatını toplayalım
    for ($i = 0; $i < count($expanded); $i += 2) {
        $total += $expanded[$i]['price'];
    }
    return $total;
}

// Sepet ürünlerini $_SESSION['cart'] dizisinden alıyoruz
$cartProducts = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Sepetteki ürünleri kampanyalı ve normal ürünler olarak ayıralım
$campaignItems = [];
$regularItems  = [];
foreach ($cartProducts as $item) {
    // Sepete eklenirken ürün bilgisine 'category' anahtarının eklenmiş olması gerekiyor
    if (isset($item['category']) && $item['category'] === 'Boyama Kitapları') {
        $campaignItems[] = $item;
    } else {
        $regularItems[] = $item;
    }
}

// Kampanyalı ürünlerin toplam tutarını hesapla (2 al 1 öde kampanyası)
$campaignTotal = 0;
if (!empty($campaignItems)) {
    $campaignTotal = calculateCampaignTotal($campaignItems);
}

// Normal ürünlerin toplamı
$regularTotal = 0;
foreach ($regularItems as $item) {
    $regularTotal += $item['price'] * $item['quantity'];
}

$grandTotal = $campaignTotal + $regularTotal;

// Ödeme işlemi: Form gönderildiğinde çalışır.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'];
    
    // Sipariş (orders) tablosuna ekleme yapılıyor
    $stmtOrder = $mysqli->prepare("INSERT INTO orders (user_id, order_date, payment_method, order_status) VALUES (?, NOW(), ?, 'Completed')");
    if (!$stmtOrder) {
        die("Order prepare error: " . $mysqli->error);
    }
    $stmtOrder->bind_param("is", $user_id, $payment_method);
    if (!$stmtOrder->execute()) {
        die("Order execute error: " . $stmtOrder->error);
    }
    $order_id = $stmtOrder->insert_id;
    $stmtOrder->close();
    
    // Sepetteki her ürün için order_details ekleniyor
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        foreach ($_SESSION['cart'] as $cartItem) {
            $stmtDetail = $mysqli->prepare("INSERT INTO order_details (order_id, product_id, quantity) VALUES (?, ?, ?)");
            if (!$stmtDetail) {
                die("Order detail prepare error: " . $mysqli->error);
            }
            $stmtDetail->bind_param("iii", $order_id, $cartItem['product_id'], $cartItem['quantity']);
            if (!$stmtDetail->execute()) {
                die("Order detail execute error: " . $stmtDetail->error);
            }
            $stmtDetail->close();
        }
    }
    
    // Ödeme başarılı, sepet temizleniyor
    unset($_SESSION['cart']);
    $payment_success = "Ödeme başarılı! Siparişiniz oluşturuldu.";
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Sepetim</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <!-- Google Fonts: Pacifico for LibHub (navbar-brand) -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
    .navbar-brand {
    font-family: 'Pacifico', cursive !important;
    font-size: 3.5rem !important;
    color:rgb(19, 150, 75) !important;
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
      margin-top: 30px;
    }
    h1 {
      font-weight: 700;
      margin-bottom: 20px;
      text-align: center;
      color: #333;
    }
    /* Sepet Tablosu */
    table {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      overflow: hidden;
    }
    .table thead th {
      background-color: #5a8f7b;
      color: #fff;
      font-weight: 600;
      text-align: center;
      border: none;
    }
    .table tbody td, .table tfoot td {
      text-align: center;
      vertical-align: middle;
    }
    .table tfoot th, .table tfoot td {
      font-size: 1.2rem;
      font-weight: 600;
      color: #5a8f7b;
    }
    /* Butonlar */
    .btn-danger {
      background-color: #e74c3c;
      border: none;
      transition: background-color 0.3s ease;
    }
    .btn-danger:hover {
      background-color: #c0392b;
    }
    .btn-success {
      background-color: #28a745;
      border: none;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .btn-success:hover {
      background-color: #218838;
      transform: translateY(-2px);
    }
    /* Ödeme Formu */
    form .form-group label {
      font-weight: 500;
    }
    .btn-block {
      padding: 0.75rem;
      font-size: 1rem;
    }
    /* Responsive Ayarlamalar */
    @media (max-width: 768px) {
      h1 {
        font-size: 1.8rem;
      }
      .table tfoot th, .table tfoot td {
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <h1>Sepetim</h1>
  <?php if (isset($payment_success)) : ?>
      <div class="alert alert-success text-center"><?php echo $payment_success; ?></div>
  <?php endif; ?>
  
  <?php if (empty($cartProducts)): ?>
      <p class="text-center">Sepetiniz boş.</p>
  <?php else: ?>
      <table class="table table-striped">
          <thead>
              <tr>
                  <th>Ürün ID</th>
                  <th>Ürün Adı</th>
                  <th>Adet</th>
                  <th>Birim Fiyat</th>
                  <th>Toplam Fiyat</th>
                  <th>İşlem</th>
              </tr>
          </thead>
          <tbody>
              <?php 
              foreach ($cartProducts as $item):
                  $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
                  $unitPrice = floatval($item['price']);
                  $total = $unitPrice * $quantity;
              ?>
              <tr>
                  <td><?php echo $item['product_id']; ?></td>
                  <td><?php echo htmlspecialchars($item['name']); ?></td>
                  <td><?php echo $quantity; ?></td>
                  <td><?php echo number_format($unitPrice, 2, ',', '.'); ?> TL</td>
                  <td><?php echo number_format($total, 2, ',', '.'); ?> TL</td>
                  <td>
                      <a href="remove_from_cart.php?id=<?php echo $item['product_id']; ?>" class="btn btn-danger btn-sm delete-cart">Ürünü Sil</a>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
          <tfoot>
              <?php if (!empty($regularItems)): ?>
              <tr>
                  <th colspan="4" class="text-right">Normal Ürün Toplamı:</th>
                  <th colspan="2"><?php echo number_format($regularTotal, 2, ',', '.'); ?> TL</th>
              </tr>
              <?php endif; ?>
              <?php if (!empty($campaignItems)): ?>
              <tr>
                  <th colspan="4" class="text-right">Kampanyalı (2 Al 1 Öde) Ürün Toplamı:</th>
                  <th colspan="2"><?php echo number_format($campaignTotal, 2, ',', '.'); ?> TL</th>
              </tr>
              <?php endif; ?>
              <tr>
                  <th colspan="4" class="text-right">Genel Toplam:</th>
                  <th colspan="2"><?php echo number_format($grandTotal, 2, ',', '.'); ?> TL</th>
              </tr>
          </tfoot>
      </table>
      <!-- Ödeme Formu -->
      <h2 class="text-center mb-4">Ödeme Yap</h2>
      <form method="post" action="cart.php">
          <div class="form-group">
              <label for="payment_method">Ödeme Yöntemi</label>
              <select name="payment_method" id="payment_method" class="form-control">
                  <option value="Havale">Havale</option>
                  <option value="Kapıda">Kapıda</option>
                  <option value="Kredi Kartı">Kredi Kartı</option>
              </select>
          </div>
          <button type="submit" name="confirm_payment" class="btn btn-success btn-block">Ödeme Yap</button>
      </form>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>

<!-- JS Kütüphaneleri -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
$(document).ready(function(){
  // Delete butonlarına SweetAlert2 onay penceresi ekleme
  $(document).on('click', '.delete-cart', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    Swal.fire({
      title: 'Sepetten bu ürünü silmek istediğinize emin misiniz?',
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
