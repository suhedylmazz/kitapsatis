<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini güvenli hale getirelim
    $username     = $mysqli->real_escape_string(trim($_POST['username']));
    $ad_soyad     = $mysqli->real_escape_string(trim($_POST['ad_soyad']));
    $email        = $mysqli->real_escape_string(trim($_POST['email']));
    $phone        = $mysqli->real_escape_string(trim($_POST['phone']));
    $password     = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Kayıt sorgusu
    $sql  = "INSERT INTO users (username, ad_soyad, email, phone, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Sorgu hatası: " . $mysqli->error);
    }

    $stmt->bind_param("sssss", $username, $ad_soyad, $email, $phone, $passwordHash);
    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        $error = $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kayıt Ol</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
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
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: 600;
      color: #333;
    }
    .form-control {
      border-radius: 5px;
    }
    .btn-primary {
      background-color: #5a8f7b;
      border: none;
      border-radius: 5px;
      width: 100%;
      transition: background-color .3s;
    }
    .btn-primary:hover {
      background-color: #507a68;
    }
    .alert {
      font-size: .9rem;
    }
    .input-group-text {
      background-color: white;
      border-left: 0;
      cursor: pointer;
    }
    .input-group .form-control {
      border-right: 0;
    }
    .input-group .form-control:focus {
      box-shadow: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Kayıt Ol</h1>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
      <div class="form-group">
        <label for="username">Kullanıcı Adı</label>
        <input type="text" id="username" name="username" class="form-control" required autofocus>
      </div>
      <div class="form-group">
        <label for="ad_soyad">Ad Soyad</label>
        <input type="text" id="ad_soyad" name="ad_soyad" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="email">E‑posta</label>
        <input type="email" id="email" name="email" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="phone">Telefon Numarası</label>
        <input type="tel" id="phone" name="phone" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="password">Şifre</label>
        <div class="input-group">
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            required
          >
          <div class="input-group-append">
            <span class="input-group-text" id="togglePassword">
              <i class="fas fa-eye-slash"></i>
            </span>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Kayıt Ol</button>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script>
    $(function(){
      $('#togglePassword').on('click', function(){
        const $pwd = $('#password');
        const type = $pwd.attr('type') === 'password' ? 'text' : 'password';
        $pwd.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
      });
    });
  </script>
</body>
</html>
