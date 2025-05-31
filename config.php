<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host     = "localhost";
$username = "root";
$password = "";
$database = "KitapSatis";

$mysqli = new mysqli($host, $username, $password, $database);
$conn   = $mysqli;

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
      "success" => false,
      "message" => "DB bağlantı hatası: " . $mysqli->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli->set_charset("utf8");

