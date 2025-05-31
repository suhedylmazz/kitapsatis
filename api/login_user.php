<?php
// api/login.php
header('Content-Type: application/json');
require_once "config.php";


// Gelen JSON’i oku
$data = json_decode(file_get_contents('php://input'), true);

$username = $conn->real_escape_string(trim($data['username'] ?? ''));
$password =           trim($data['password'] ?? '');

// Alan kontrolü
if (!$username || !$password) {
    echo json_encode([
      'success' => false,
      'message' => 'Kullanıcı adı ve şifre gerekli.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kullanıcıyı sorgula
$sql = "
  SELECT id, username, password,  email, phone
  FROM users
  WHERE username = '$username'
  LIMIT 1
";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    // Şifre eşleşiyor mu?
    if (password_verify($password, $row['password'])) {
        // Başarılı giriş
        echo json_encode([
          'success' => true,
          'message' => 'Giriş başarılı.',
          'user'    => [
            'id'         => (int)$row['id'],
            'username'   => $row['username'],
            //'first_name' => $row['first_name'],
            //'last_name'  => $row['last_name'],
            'email'      => $row['email'],
            'phone'      => $row['phone']
          ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Şifre yanlış
        echo json_encode([
          'success' => false,
          'message' => 'Yanlış şifre.'
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // Kullanıcı bulunamadı
    echo json_encode([
      'success' => false,
      'message' => 'Bu kullanıcı adı bulunamadı.'
    ], JSON_UNESCAPED_UNICODE);
}
