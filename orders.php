<?php
session_start();
require_once 'functions.php';
require_once 'config.php'; // $conn

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcının siparişlerini çekiyoruz.
$sql = "SELECT id, order_date, payment_method, order_status FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
   <meta charset="UTF-8">
   <title>Siparişlerim</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <h1>Siparişlerim</h1>
  <?php if($result->num_rows > 0): ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Sipariş No</th>
          <th>Tarih</th>
          <th>Ödeme Yöntemi</th>
          <th>Durum</th>
          <th>Detay</th>
        </tr>
      </thead>
      <tbody>
        <?php while($order = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $order['id']; ?></td>
            <td><?php echo $order['order_date']; ?></td>
            <td><?php echo $order['payment_method']; ?></td>
            <td><?php echo $order['order_status']; ?></td>
            <td>
              <!-- order_detail.php dosyasını sipariş detayları için oluşturabilirsiniz -->
              <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Detay</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Henüz siparişiniz bulunmamaktadır.</p>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
