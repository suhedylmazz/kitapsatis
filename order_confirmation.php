<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = intval($_GET['order_id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Sipariş Onay</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <div class="alert alert-success">
    <h4>Teşekkürler!</h4>
    <p>Siparişiniz oluşturuldu. Sipariş No: <strong><?php echo $order_id; ?></strong></p>
    <p><a href="orders.php" class="btn btn-primary">Siparişlerimi Görüntüle</a></p>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
