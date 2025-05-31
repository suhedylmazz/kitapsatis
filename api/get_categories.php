<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

mysqli_set_charset($conn, 'utf8');

$data = [];

// Önce ana kategorileri çekiyoruz (parent_id IS NULL veya 0 olanlar)
$sql = "SELECT id, category_name FROM categories WHERE parent_id IS NULL OR parent_id = 0";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $category_id = $row['id'];

        // Alt kategorileri çek
        $subcategories = [];
        $sub_sql = "SELECT id, category_name FROM categories WHERE parent_id = $category_id";
        if ($sub_result = mysqli_query($conn, $sub_sql)) {
            while ($sub_row = mysqli_fetch_assoc($sub_result)) {
                $subcategories[] = $sub_row;
            }
        }

        // Ana kategoriye alt kategorileri ekle
        $row['subcategories'] = $subcategories;

        // Ana kategori + alt kategoriler
        $data[] = $row;
    }
}

// JSON çıktısı
echo json_encode(
    ["categories" => $data],
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);
exit;
?>
