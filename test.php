<?php
require_once 'config.php'; // veritabanı bağlantı kodlarınızın olduğu dosya

$sql = "SELECT 1";
$result = $mysqli->query($sql);

if ($result) {
    echo "Veritabanına başarıyla bağlanıldı ve test sorgusu çalıştı!";
} else {
    echo "Bağlantı veya sorgu hatası: " . $mysqli->error;
}
?>
