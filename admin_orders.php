<?php
var_dump($conn);

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'functions.php';

// Admin girişi kontrolü
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Siparişleri, kullanıcı adlarıyla birlikte çekiyoruz.
// LEFT JOIN kullanıyoruz; böylece orders tablosundaki her sipariş, kullanıcısı olmasa da listelenir.
$sql = "SELECT o.id, o.order_date, o.payment_method, o.order_status, u.username 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
if(!$stmt) {
    die("Sorgu hazırlanamadı: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();

// Debug: Kayıt sayısını yazdırıyoruz.
echo "<p>Sipariş sayısı: " . $result->num_rows . "</p>";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
   <meta charset="UTF-8">
   <title>Admin Sipariş Listesi</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="container mt-4">
  <h1>Sipariş Listesi</h1>
  <?php if($result->num_rows > 0): ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Sipariş No</th>
          <th>Tarih</th>
          <th>Müşteri</th>
          <th>Ödeme Yöntemi</th>
          <th>Sipariş Durumu</th>
          <th>Detay</th>
        </tr>
      </thead>
      <tbody>
        <?php while($order = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $order['id']; ?></td>
            <td><?php echo $order['order_date']; ?></td>
            <td><?php echo htmlspecialchars($order['username'] ?? 'Bilinmiyor'); ?></td>
            <td><?php echo $order['payment_method']; ?></td>
            <td><?php echo $order['order_status']; ?></td>
            <td>
              <a href="admin_order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Detay</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Henüz sipariş bulunmamaktadır.</p>
  <?php endif; ?>
</div>
<?php include 'admin_footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
