<?php
session_start();
require_once 'config.php';
if(!isset($_SESSION['admin_id'])) {
  die("Admin girişi gerekli.");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0) {
  die("Geçersiz ID");
}

// Form gönderildiyse
if(isset($_POST['save'])) {
  $name = $_POST['category_name'];
  $desc = $_POST['category_desc'];
  
  $sql = "UPDATE categories SET category_name=?, category_desc=? WHERE id=?";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param("ssi", $name, $desc, $id);
  $stmt->execute();
  header("Location: admin_categories.php");
  exit;
}

// Kategori bilgilerini çek
$sql = "SELECT * FROM categories WHERE id=?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$cat = $res->fetch_assoc();
if(!$cat) {
  die("Kategori bulunamadı.");
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Kategori Düzenle</title>
</head>
<body>
<h1>Kategori Düzenle</h1>
<form method="post">
  <div>
    <label>Kategori Adı:</label>
    <input type="text" name="category_name" value="<?php echo htmlspecialchars($cat['category_name']); ?>">
  </div>
  <div>
    <label>Açıklama:</label>
    <textarea name="category_desc"><?php echo htmlspecialchars($cat['category_desc']); ?></textarea>
  </div>
  <button type="submit" name="save">Kaydet</button>
</form>
</body>
</html>
