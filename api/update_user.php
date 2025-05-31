<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config.php'; // Veritabanı bağlantısı

// Admin kontrolü
if (!isAdmin()) {
    echo json_encode(["error" => "Admin yetkisi gereklidir."]);
    exit;
}

// Kullanıcı ID kontrolü
if (!isset($_GET['id'])) {
    echo json_encode(["error" => "Kullanıcı ID belirtilmedi."]);
    exit;
}

$user_id = intval($_GET['id']);
if ($user_id <= 0) {
    echo json_encode(["error" => "Geçersiz kullanıcı ID."]);
    exit;
}

// Kullanıcı bilgilerini veritabanından çekelim
$stmt = $conn->prepare("SELECT username, ad_soyad, email, phone, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$user = $result->fetch_assoc()) {
    echo json_encode(["error" => "Kullanıcı bulunamadı."]);
    exit;
}
$stmt->close();

// Kullanıcı bilgilerini güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formdan gelen veriler
    $username = $conn->real_escape_string(trim($_POST['username']));
    $ad_soyad = $conn->real_escape_string(trim($_POST['ad_soyad']));
    $email    = $conn->real_escape_string(trim($_POST['email']));
    $phone    = $conn->real_escape_string(trim($_POST['phone']));
    $role     = $conn->real_escape_string(trim($_POST['role']));

    // Veritabanı güncelleme sorgusu
    $stmtUpdate = $conn->prepare("UPDATE users SET username = ?, ad_soyad = ?, email = ?, phone = ?, role = ? WHERE id = ?");
    if (!$stmtUpdate) {
        echo json_encode(["error" => "Güncelleme sorgu hatası: " . $conn->error]);
        exit;
    }

    // Parametrelerin bağlanması
    $stmtUpdate->bind_param("sssssi", $username, $ad_soyad, $email, $phone, $role, $user_id);

    if ($stmtUpdate->execute()) {
        // Başarılı güncelleme
        echo json_encode([
            "success" => true,
            "message" => "Kullanıcı bilgileri başarıyla güncellendi.",
            "updated_user" => [
                "id" => $user_id,
                "username" => $username,
                "ad_soyad" => $ad_soyad,
                "email" => $email,
                "phone" => $phone,
                "role" => $role
            ]
        ]);
    } else {
        echo json_encode(["error" => "Güncelleme hatası: " . $stmtUpdate->error]);
    }

    $stmtUpdate->close();
} else {
    echo json_encode(["error" => "Geçersiz istek yöntemi. POST istekleri bekleniyor."]);
}
?>
