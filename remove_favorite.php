<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $user_id    = $_SESSION['user_id'];

    global $mysqli;
    // Veritabanından favoriyi sil
    $stmt = $mysqli->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
}

// Yönlendirme (redirect parametresi varsa)
if (isset($_GET['redirect'])) {
    header("Location: " . $_GET['redirect']);
} else {
    header("Location: favorites.php");
}
exit;
?>
