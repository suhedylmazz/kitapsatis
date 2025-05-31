<?php
session_start();
require_once 'functions.php'; // functions.php içinde session_start(), config.php ve isAdmin() tanımlı olsun.

// Admin kontrolü
if (!isAdmin()) {
    header("Location: admin_login.php");
    exit;
}

// Kullanıcı işlemleri (promote / delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    if ($action == 'promote') {
        // Kullanıcıyı admin yap
        $sql = "UPDATE users SET role='admin' WHERE id = $id";
        $mysqli->query($sql);
    } elseif ($action == 'delete') {
        // Admin kendi hesabını silemesin
        if ($id != $_SESSION['admin_id']) {
            $sql = "DELETE FROM users WHERE id = $id";
            $mysqli->query($sql);
        }
    }
}

// Kullanıcıları çekiyoruz (ad_soyad, email, phone sütunlarını da alıyoruz)
$query = "SELECT id, username, ad_soyad, email, phone, role, created_at FROM users ORDER BY id ASC";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <a href="admin_users.php">Kullanıcı Yönetimi</a>

    <meta charset="UTF-8">
    <title>Admin Paneli - Kullanıcı Yönetimi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
          integrity="sha512-pKpD/0QykBlsY3R2C5HPr6QXxSMmBG5p9e45PspQp0fL9XvdfK4xMxn/IV0qElPZ0+33v4m5R+6x1IxhO3v+FA==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
      body {
          font-family: 'Poppins', sans-serif;
          background-color: #f8f9fa;
          padding: 20px;
      }
      h1 {
          margin-bottom: 20px;
          text-align: center;
          color: #333;
      }
      table {
          background-color: #fff;
          border-radius: 8px;
          box-shadow: 0 2px 6px rgba(0,0,0,0.1);
          width: 100%;
      }
      th, td {
          vertical-align: middle !important;
          text-align: center;
      }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h1>Kullanıcı Yönetimi</h1>
    <p>Hoşgeldiniz, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
    
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Kullanıcı Adı</th>
          <th>Ad Soyad</th>
          <th>E-posta</th>
          <th>Telefon</th>
          <th>Rol</th>
          <th>Kayıt Tarihi</th>
          <th>İşlemler</th>
        </tr>
      </thead>
      <tbody>
        <?php if($result && $result->num_rows > 0): ?>
           <?php while($user = $result->fetch_assoc()): ?>
           <tr>
              <td><?php echo $user['id']; ?></td>
              <td><?php echo htmlspecialchars($user['username']); ?></td>
              <td><?php echo htmlspecialchars($user['ad_soyad']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo htmlspecialchars($user['phone']); ?></td>
              <td><?php echo htmlspecialchars($user['role']); ?></td>
              <td><?php echo htmlspecialchars($user['created_at']); ?></td>
              <td>
                <!-- Güncelle Butonu -->
                <a href="update_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                  <i class="fa fa-edit"></i> Güncelle
                </a>
                <!-- Sil Butonu (Admin kendi hesabını silemesin) -->
                <?php if($user['id'] != $_SESSION['admin_id']): ?>
                  <a href="#" class="btn btn-danger btn-sm delete-user" data-userid="<?php echo $user['id']; ?>">
                    <i class="fa fa-trash"></i> Sil
                  </a>
                <?php endif; ?>
              </td>
           </tr>
           <?php endwhile; ?>
        <?php else: ?>
           <tr>
             <td colspan="8" class="text-center">Kayıtlı kullanıcı bulunamadı.</td>
           </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <a href="admin_logout.php" class="btn btn-secondary mt-3">Çıkış Yap</a>
</div>
<?php include 'footer.php'; ?>

<!-- jQuery ve Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
function deleteUser(userId) {
  Swal.fire({
    title: 'Kullanıcıyı silmek istediğinize emin misiniz?',
    text: 'Bu işlemi geri alamazsınız!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Evet, Sil',
    cancelButtonText: 'İptal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'admin.php?action=delete&id=' + userId;
    }
  });
}

$(document).on('click', '.delete-user', function(e) {
    e.preventDefault();
    var userId = $(this).data('userid');
    deleteUser(userId);
});
</script>
</body>
</html>
