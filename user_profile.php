<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çekelim (kayıt sırasında girilen bilgiler: username, ad_soyad, email, phone, address)
$stmtUser = $mysqli->prepare("SELECT username, ad_soyad, email, phone, address FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$stmtUser->close();

// Eğer form gönderildiyse, adres bilgisini güncelleyelim
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_address'])) {
    $address = $_POST['address'] ?? '';
    $stmtUpdate = $mysqli->prepare("UPDATE users SET address = ? WHERE id = ?");
    $stmtUpdate->bind_param("si", $address, $user_id);
    if ($stmtUpdate->execute()) {
        $message = "Adresiniz güncellendi.";
        // Güncellenen bilgileri tekrar çekelim
        $stmtUser = $mysqli->prepare("SELECT username, ad_soyad, email, phone, address FROM users WHERE id = ?");
        $stmtUser->bind_param("i", $user_id);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();
        $user = $resultUser->fetch_assoc();
        $stmtUser->close();
    } else {
        $message = "Adres güncellemede bir hata oluştu.";
    }
    $stmtUpdate->close();
}

// Kullanıcının siparişlerini çekelim (orders tablosunda user_id eşleşsin)
$stmtOrders = $mysqli->prepare("SELECT id, order_date, payment_method, order_status FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmtOrders->bind_param("i", $user_id);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();
$orders = [];
while ($row = $resultOrders->fetch_assoc()) {
    $orders[] = $row;
}
$stmtOrders->close();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kullanıcı Profilim</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .profile-container {
      margin-top: 30px;
    }
    .profile-header {
      padding: 20px;
      background: #5a8f7b;
      color: #fff;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
    }
    .card {
      margin-bottom: 20px;
    }
    .order-table th, .order-table td {
      text-align: center;
    }
  </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container profile-container">
  <div class="profile-header">
    <h2>Hoşgeldin, <?php echo htmlspecialchars($user['ad_soyad']); ?>!</h2>
  </div>
  
  <?php if(isset($message)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  
  <!-- Profil Bilgileri Kartı -->
  <div class="card">
    <div class="card-header">
      Profil Bilgilerim
    </div>
    <div class="card-body">
      <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
      <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($user['ad_soyad']); ?></p>
      <p><strong>E-posta:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
      <p><strong>Telefon:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
      <p>
        <strong>Adres:</strong> 
        <?php 
          echo !empty($user['address']) ? htmlspecialchars($user['address']) : '<em>Henüz eklenmedi.</em>'; 
        ?>
      </p>
      <!-- Adres Güncelleme Formu -->
      <form method="post" action="user_profile.php">
        <div class="form-group">
          <label for="address">Adresinizi Güncelleyin</label>
          <textarea name="address" id="address" class="form-control" rows="3" placeholder="Adresinizi giriniz..."><?php echo htmlspecialchars($user['address']); ?></textarea>
        </div>
        <button type="submit" name="update_address" class="btn btn-primary">Güncelle</button>
      </form>
    </div>
  </div>
  
  <!-- Siparişlerim Kartı -->
  <div class="card">
    <div class="card-header">
      Siparişlerim
    </div>
    <div class="card-body">
      <?php if(empty($orders)): ?>
        <p>Henüz siparişiniz bulunmamaktadır.</p>
      <?php else: ?>
        <table class="table order-table">
          <thead>
            <tr>
              <th>Sipariş ID</th>
              <th>Tarih</th>
              <th>Ödeme Yöntemi</th>
              <th>Durum</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($orders as $order): ?>
              <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['order_date']; ?></td>
                <td><?php echo $order['payment_method']; ?></td>
                <td><?php echo $order['order_status']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
  
</div>
<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
