<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql  = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Sorgu hatası: " . $mysqli->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Hatalı şifre!";
        }
    } else {
        $error = "Kullanıcı bulunamadı!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Giriş Yap</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: #e9ecef;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
    }
    .login-container {
      max-width: 400px;
      margin: 80px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .login-container h1 {
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
  <div class="login-container">
    <h1>Giriş Yap</h1>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
      <div class="form-group">
        <label for="username">Kullanıcı Adı</label>
        <input type="text" id="username" name="username" class="form-control" required autofocus>
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
  <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
    <i class="fas fa-eye-slash"></i>
  </span>
</div>

        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script>
$("#togglePassword").on('click', function() {
  const input = $("#password");
  const icon  = $(this).find("i");
  if (input.attr("type") === "password") {
    input.attr("type", "text");
    icon.removeClass("fa-eye-slash").addClass("fa-eye");
  } else {
    input.attr("type", "password");
    icon.removeClass("fa-eye").addClass("fa-eye-slash");
  }
});

  </script>
</body>
</html>
  