<?php
session_start();    // Oturumu başlatıyoruz
session_destroy();  // Oturumu sonlandırıyoruz
header("Location: index.php"); // Ana sayfaya yönlendiriyoruz
exit;
?>

