<?php
$host = "localhost";
$user = "root";
$pass = "root";
$db = "devodoro";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}
?>