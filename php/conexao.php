<?php
ini_set("default_charset", "UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$db = "devodoro";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    die("Erro ao configurar charset: " . $conn->error);
}
?>
