<?php
session_start();
require_once 'functions.php'; // functions.php içinde session_start(), config.php ve isAdmin() tanımlı olsun.

// Admin kontrolü
if (!isAdmin()) {
    header("Location: admin_login.php");
    exit;
}

// Ürün ve kullanıcı işlemleri
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    if ($action == 'promote') {
        // Kullanıcıyı admin yap (örneğin, role sütununu güncelliyoruz)
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <!-- Bootstrap CSS -->
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
    <!-- Google Fonts: Pacifico for LibHub (navbar-brand) -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
         .navbar-brand {
    font-family: 'Pacifico', cursive !important;
    font-size: 3.5rem !important;
    color:rgb(19, 150, 75) !important;
    text-shadow: 3px 2px 4px rgba(0,0,0,0.3) !important;
  }
      /* Genel Stil */
      body {
          font-family: 'Poppins', sans-serif;
          background-color: #f0f2f5;
          margin: 0;
          padding: 20px;
      }
      h1, h3 {
          color: #333;
      }
      h1 {
          margin-bottom: 30px;
          text-align: center;
      }
      /* Container */
      .container {
          background: #fff;
          padding: 30px;
          border-radius: 10px;
          box-shadow: 0 4px 12px rgba(0,0,0,0.1);
          max-width: 1200px;
          margin: 20px auto;
      }
      /* Nav Tabs */
      .nav-tabs .nav-link {
          color: #555;
          font-weight: 500;
          border: none;
          border-bottom: 2px solid transparent;
      }
      .nav-tabs .nav-link:hover {
          color: #333;
      }
      .nav-tabs .nav-link.active {
          color: #5a8f7b;
          border-bottom: 2px solid #5a8f7b;
          background-color: transparent;
      }
      .tab-content {
          margin-top: 20px;
      }
      /* Tablo Stili */
      table {
          background-color: #fff;
          border-collapse: collapse;
          width: 100%;
      }
      th, td {
          padding: 12px 15px;
          border: 1px solid #ddd;
      }
      th {
          background-color: #5a8f7b;
          color: #fff;
          text-align: center;
      }
      td {
          text-align: center;
      }
      tr:nth-child(even) {
          background-color: #f8f9fa;
      }
      /* Butonlar */
      .btn {
          border-radius: 5px;
          font-size: 0.9rem;
          padding: 6px 12px;
          margin: 2px;
      }
      .btn-primary {
          background-color: #5a8f7b;
          border: none;
          transition: background-color 0.3s ease;
      }
      .btn-primary:hover {
          background-color: #507a68;
      }
      .btn-warning {
          background-color: #ffc107;
          border: none;
          transition: background-color 0.3s ease;
      }
      .btn-warning:hover {
          background-color: #e0a800;
      }
      .btn-danger {
          background-color: #e74c3c;
          border: none;
          transition: background-color 0.3s ease;
      }
      .btn-danger:hover {
          background-color: #c0392b;
      }
      .btn-secondary {
          background-color: #6c757d;
          border: none;
          transition: background-color 0.3s ease;
      }
      .btn-secondary:hover {
          background-color: #5a6268;
      }
      /* Genel Link Stili */
      a {
          text-decoration: none;
      }
      /* Responsive */
      @media (max-width: 768px) {
          h1 {
              font-size: 1.8rem;
          }
          .container {
              padding: 20px;
          }
          th, td {
              padding: 8px 10px;
          }
      }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Admin Paneli</h1>
    <p class="text-center">Hoşgeldiniz, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
    
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs" id="adminTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab" aria-controls="product" aria-selected="true">
                Ürün Yönetimi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="user-tab" data-toggle="tab" href="#user" role="tab" aria-controls="user" aria-selected="false">
                Kullanıcı Yönetimi
            </a>
        </li>
    </ul>
    
    <div class="tab-content" id="adminTabContent">
        <!-- Ürün Yönetimi Bölümü -->
        <div class="tab-pane fade show active" id="product" role="tabpanel" aria-labelledby="product-tab">
            <h3 class="mt-3">Ürün Ekle</h3>
            <?php
            // Ürün ekleme işlemi
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
                $name             = $_POST['name'];
                $description      = $_POST['description'];
                $price            = $_POST['price'];
                $category         = $_POST['category']; 
                $language         = $_POST['language'];
                $publication_year = $_POST['publication_year'];
                $page_count       = $_POST['page_count'];
                $image            = $_POST['image'];
                $author           = $_POST['author'];
                $publisher        = $_POST['publisher'];
                
                // Alt kategori ID bulma (sadece alt kategoriler kabul ediliyor)
                $stmtCat = $mysqli->prepare("SELECT id FROM categories WHERE category_name = ? AND parent_id IS NOT NULL");
                if (!$stmtCat) {
                    die("Kategori sorgu hatası: " . $mysqli->error);
                }
                $stmtCat->bind_param("s", $category);
                $stmtCat->execute();
                $stmtCat->bind_result($categoryId);
                if (!$stmtCat->fetch()) {
                    die("Girilen kategori bulunamadı. Lütfen alt kategori giriniz.");
                }
                $stmtCat->close();
                
                // Ürün ekleme sorgusu
                $stmt = $mysqli->prepare("INSERT INTO products (name, description, price, category_id, language, publication_year, page_count, image_url, author, publisher)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssdssissss", $name, $description, $price, $categoryId, $language, $publication_year, $page_count, $image, $author, $publisher);
                    if ($stmt->execute()) {
                        echo '<div class="alert alert-success">Ürün başarıyla eklendi.</div>';
                    } else {
                        echo '<div class="alert alert-danger">Ürün eklenemedi: ' . $stmt->error . '</div>';
                    }
                    $stmt->close();
                } else {
                    echo '<div class="alert alert-danger">Sorgu hatası: ' . $mysqli->error . '</div>';
                }
            }
            ?>
            <form method="post">
              <div class="form-group">
                <label for="name">Ürün Adı</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="description">Açıklama</label>
                <textarea name="description" class="form-control" required></textarea>
              </div>
              <div class="form-group">
                <label for="price">Fiyat</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="category">Kategori Adı</label>
                <input type="text" name="category" class="form-control" required>
                <small>Lütfen alt kategori adını girin (örn: Roman, Edebiyat, vb.).</small>
              </div>
              <div class="form-group">
                <label for="language">Dil</label>
                <input type="text" name="language" class="form-control">
              </div>
              <div class="form-group">
                <label for="publication_year">Baskı Yılı</label>
                <input type="number" name="publication_year" class="form-control">
              </div>
              <div class="form-group">
                <label for="page_count">Sayfa Sayısı</label>
                <input type="number" name="page_count" class="form-control">
              </div>
              <div class="form-group">
                <label for="image">Resim URL</label>
                <input type="text" name="image" class="form-control">
              </div>
              <div class="form-group">
                <label for="author">Yazar</label>
                <input type="text" name="author" class="form-control">
              </div>
              <div class="form-group">
                <label for="publisher">Yayınevi</label>
                <input type="text" name="publisher" class="form-control">
              </div>
              <button type="submit" name="add_product" class="btn btn-success">Ürün Ekle</button>
            </form>
            <hr>
            <h3>Ürün Listesi</h3>
            <?php
            $prodResult = $mysqli->query("SELECT * FROM products");
            ?>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Ürün Adı</th>
                  <th>Kategori ID</th>
                  <th>Yazar</th>
                  <th>Yayınevi</th>
                  <th>Fiyat</th>
                  <th>İşlem</th>
                </tr>
              </thead>
              <tbody>
                <?php while($prod = $prodResult->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $prod['id']; ?></td>
                  <td><?php echo htmlspecialchars($prod['name']); ?></td>
                  <td><?php echo htmlspecialchars($prod['category_id']); ?></td>
                  <td><?php echo htmlspecialchars($prod['author']); ?></td>
                  <td><?php echo htmlspecialchars($prod['publisher']); ?></td>
                  <td><?php echo $prod['price']; ?> TL</td>
                  <td>
                    <a href="update_product.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary btn-sm">
                      <i class="fa fa-edit"></i> Düzenle
                    </a>
                    <a href="delete_product.php?id=<?php echo $prod['id']; ?>" class="btn btn-danger btn-sm">
                      <i class="fa fa-trash"></i> Sil
                    </a>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
        </div>
        
        <!-- Kullanıcı Yönetimi Bölümü -->
        <div class="tab-pane fade" id="user" role="tabpanel" aria-labelledby="user-tab">
            <h3 class="mt-3">Kullanıcı Yönetimi</h3>
            <?php
            $userResult = $mysqli->query("SELECT * FROM users ORDER BY id ASC");
            ?>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Kullanıcı Adı</th>
                  <th>Ad Soyad</th>
                  <th>E-posta</th>
                  <th>Telefon</th>
                  <th>Rol</th>
                  <th>İşlemler</th>
                </tr>
              </thead>
              <tbody>
                <?php while($user = $userResult->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $user['id']; ?></td>
                  <td><?php echo htmlspecialchars($user['username']); ?></td>
                  <td><?php echo htmlspecialchars($user['ad_soyad']); ?></td>
                  <td><?php echo htmlspecialchars($user['email']); ?></td>
                  <td><?php echo htmlspecialchars($user['phone']); ?></td>
                  <td><?php echo htmlspecialchars($user['role']); ?></td>
                  <td>
                    <a href="update_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                      <i class="fa fa-edit"></i> Güncelle
                    </a>
                    <?php if($user['id'] != $_SESSION['admin_id']): ?>
                      <a href="#" class="btn btn-danger btn-sm delete-user" data-userid="<?php echo $user['id']; ?>">
                        <i class="fa fa-trash"></i> Sil
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
        </div>
    </div>
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
