<?php
// sign_up.php
header('Content-Type: application/json');
error_reporting(0);      // üretimde hata raporlamayı kapatın
session_start();
require_once 'config.php';
require_once 'functions.php';

// Sadece POST izinli
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode([
    'success' => false,
    'message' => 'Method not allowed'
  ]);
  exit;
}

// JSON gövdeyi al
$body = json_decode(file_get_contents('php://input'), true);
$username   = trim($body['username']   ?? '');
$password   = trim($body['password']   ?? '');
$first_name = trim($body['first_name'] ?? '');
$last_name  = trim($body['last_name']  ?? '');
$email      = trim($body['email']      ?? '');

// Basit validasyon
if (!$username || !$password || !$first_name || !$last_name || !$email) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => 'Lütfen tüm alanları doldurun.'
  ]);
  exit;
}

// Aynı kullanıcı var mı?
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username=? OR email=?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  http_response_code(409);
  echo json_encode([
    'success' => false,
    'message' => 'Bu kullanıcı zaten kayıtlı.'
  ]);
  exit;
}
$stmt->close();

// Yeni kullanıcıyı ekle
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("
  INSERT INTO users (username, password, first_name, last_name, email)
  VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssss",
  $username, $hash, $first_name, $last_name, $email
);
if (! $stmt->execute()) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Kayıt yapılırken bir hata oluştu.'
  ]);
  exit;
}
$stmt->close();

// Başarılı kayıt
http_response_code(201);
echo json_encode([
  'success' => true,
  'message' => 'Kayıt başarılı!'
]);
