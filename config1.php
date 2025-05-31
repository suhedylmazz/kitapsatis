<?php
$host = "localhost";  // Hostname (genellikle localhost)
$username = "root";   // XAMPP için genellikle "root" kullanıcı adı
$password = "";       // XAMPP'de varsayılan şifre boş
$database = "KitapSatis"; // Veritabanı adınız
$port = 3307; // Portu doğru girin (eğer 3307 ise)
$mysqli = new mysqli($host, $username, $password, $database, $port);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "DB bağlantı hatası: " . $mysqli->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli->set_charset("utf8");
?>
