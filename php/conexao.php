<?php
ini_set("default_charset", "UTF-8");
mysqli_report(MYSQLI_REPORT_OFF);

if (!function_exists("conexaoFalhou")) {
function conexaoFalhou($message) {
    $accept = $_SERVER["HTTP_ACCEPT"] ?? "";
    $uri = $_SERVER["REQUEST_URI"] ?? "";
    $isApi = strpos($uri, "/api/") !== false || stripos($accept, "application/json") !== false;

    if ($isApi) {
        if (!headers_sent()) {
            header("Content-Type: application/json; charset=utf-8");
        }

        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    die($message);
}
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "devodoro";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    conexaoFalhou("Erro ao conectar ao banco de dados.");
}

if (!$conn->set_charset("utf8mb4")) {
    conexaoFalhou("Erro ao configurar charset do banco de dados.");
}
?>
