<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Admin kontrolü
if (!isAdmin()) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Kullanıcı ID belirtilmedi.");
}

$user_id = intval($_GET['id']);
if ($user_id <= 0) {
    die("Geçersiz kullanıcı ID.");
}

// Kullanıcı bilgilerini veritabanından çekelim
$stmt = $mysqli->prepare("SELECT username, ad_soyad, email, phone, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$user = $result->fetch_assoc()) {
    die("Kullanıcı bulunamadı.");
}
$stmt->close();

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $username = $mysqli->real_escape_string(trim($_POST['username']));
    $ad_soyad = $mysqli->real_escape_string(trim($_POST['ad_soyad']));
    $email    = $mysqli->real_escape_string(trim($_POST['email']));
    $phone    = $mysqli->real_escape_string(trim($_POST['phone']));
    $role     = $mysqli->real_escape_string(trim($_POST['role']));

    $stmtUpdate = $mysqli->prepare("UPDATE users SET username = ?, ad_soyad = ?, email = ?, phone = ?, role = ? WHERE id = ?");
    if (!$stmtUpdate) {
        die("Güncelleme sorgu hatası: " . $mysqli->error);
    }
    $stmtUpdate->bind_param("sssssi", $username, $ad_soyad, $email, $phone, $role, $user_id);
    if ($stmtUpdate->execute()) {
        $success = "Kullanıcı bilgileri başarıyla güncellendi.";
        // Güncellenen bilgileri tekrar çekelim:
        $user['username'] = $username;
        $user['ad_soyad'] = $ad_soyad;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['role'] = $role;
    } else {
        $error = "Güncelleme hatası: " . $stmtUpdate->error;
    }
    $stmtUpdate->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kullanıcı Bilgilerini Güncelle</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
   <!-- Google Fonts: Pacifico for LibHub (navbar-brand) -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
     .navbar-brand {
    font-family: 'Pacifico', cursive !important;
    font-size: 3.5rem !important;
    color:rgb(19, 150, 75) !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3) !important;
  }
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .container {
      max-width: 500px;
      margin-top: 50px;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
  </style>
</head>
<body>
<div class="container">
  <h1>Kullanıcı Bilgilerini Güncelle</h1>
  <?php if(isset($success)): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>
  <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <div class="form-group">
      <label for="username">Kullanıcı Adı:</label>
      <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>
    <div class="form-group">
      <label for="ad_soyad">Ad Soyad:</label>
      <input type="text" name="ad_soyad" id="ad_soyad" class="form-control" value="<?php echo htmlspecialchars($user['ad_soyad']); ?>" required>
    </div>
    <div class="form-group">
      <label for="email">E-posta:</label>
      <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>
    <div class="form-group">
      <label for="phone">Telefon:</label>
      <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
    </div>
    <div class="form-group">
      <label for="role">Rol:</label>
      <select name="role" id="role" class="form-control">
          <option value="user" <?php if($user['role'] == 'user') echo 'selected'; ?>>Kullanıcı</option>
          <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
      </select>
    </div>
    <button type="submit" name="update_user" class="btn btn-primary btn-block">Güncelle</button>
  </form>
</div>
</body>
</html>
