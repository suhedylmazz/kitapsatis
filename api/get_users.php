<?php
require_once 'config.php';

$sql = "SELECT id, username, email, phone, role FROM users";
$result = $conn->query($sql);

$users = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(["success" => true, "users" => $users]);
} else {
    echo json_encode(["success" => false, "message" => "Kullanıcı bulunamadı."]);
}
$conn->close();
?>
