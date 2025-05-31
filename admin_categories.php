<?php
session_start();
require_once 'config.php';
// Admin girişi kontrolü
if(!isset($_SESSION['admin_id'])) {
  die("Admin girişi gerekli.");
}

// Kategorileri çek
$sql = "SELECT * FROM categories ORDER BY id DESC";
$res = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Kategori Yönetimi</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<h1>Kategori Yönetimi</h1>
<a href="admin_category_add.php" class="btn btn-success mb-3">Yeni Kategori Ekle</a>
<table class="table table-bordered">
  <thead>
    <tr><th>ID</th><th>Ad</th><th>Açıklama</th><th>İşlem</th></tr>
  </thead>
  <tbody>
    <?php while($cat = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo $cat['id']; ?></td>
        <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
        <td><?php echo htmlspecialchars($cat['category_desc']); ?></td>
        <td>
          <a href="admin_category_edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-primary btn-sm">Düzenle</a>
          <a href="admin_category_delete.php?id=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm">Sil</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
</body>
</html>
