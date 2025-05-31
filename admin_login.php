<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php'; // Veritabanı bağlantısı (varsa)
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kullanıcıdan gelen bilgileri alıyoruz
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Sabit admin bilgileri (hard-coded)
    $adminUsername = "adminler";
    $adminPassword = "admin123"; // Not: Gerçek uygulamalarda şifreleri düz metin olarak saklamayın!
    
    if ($username === $adminUsername && $password === $adminPassword) {
        // Giriş başarılı: Oturum bilgilerini ayarlıyoruz
        $_SESSION['admin_id'] = 1; // Örnek olarak 1 atandı
        $_SESSION['admin_username'] = $adminUsername;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Hatalı kullanıcı adı veya şifre!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Admin Giriş</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  
  <style>
    body {
      background-color: #f2f2f2;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 400px;
      margin: 80px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: 600;
      color: #333;
    }
    .form-control {
      border-radius: 5px;
      font-size: 0.95rem;
    }
    .btn-primary {
      background-color: #5a8f7b;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      padding: 0.75rem;
      transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #507a68;
    }
    .alert {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
<div class="container">
  <h1>Admin Giriş</h1>
  <?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="post" action="admin_login.php">
    <div class="form-group">
      <label for="username">Kullanıcı Adı:</label>
      <input type="text" name="username" id="username" class="form-control" required autofocus>
    </div>
    <div class="form-group">
      <label for="password">Şifre:</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
  </form>
</div>
</body>
</html>
