<?php
session_start();
require_once 'config.php';

// Giriş yapmış admin kontrolü
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

// Kullanıcı işlemleri: 'promote' (admin yap) veya 'delete' (kullanıcıyı sil)
if(isset($_GET['action']) && isset($_GET['id'])){
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    
    if($action == 'promote'){
        // Sadece admin olmayan kullanıcılar için
        $sql = "UPDATE users SET role='admin' WHERE id=$user_id";
        if($mysqli->query($sql)){
            $msg = "Kullanıcı admin olarak güncellendi.";
        } else {
            $msg = "Hata: " . $mysqli->error;
        }
    } elseif($action == 'delete'){
        // Admin kendisini silmesin
        if($user_id != $_SESSION['admin_id']){
            $sql = "DELETE FROM users WHERE id=$user_id";
            if($mysqli->query($sql)){
                $msg = "Kullanıcı silindi.";
            } else {
                $msg = "Hata: " . $mysqli->error;
            }
        } else {
            $msg = "Kendinizi silemezsiniz.";
        }
    }
}

// Tüm kullanıcıları listele (isteğe bağlı, admin'i de listeleyebilirsiniz)
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Admin Paneli</h1>
    <?php if(isset($msg)): ?>
        <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kullanıcı Adı</th>
                <th>Rol</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <?php if($user['role'] !== 'admin'): ?>
                            <a href="admin_panel.php?action=promote&id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Admin Yap</a>
                        <?php endif; ?>
                    
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="admin_logout.php" class="btn btn-secondary">Çıkış Yap</a>
</div>
</body>
</html>
