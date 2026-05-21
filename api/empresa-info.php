<?php
include '../php/conexao.php';
session_start();

header("Content-Type: application/json");

$id_empresa = $_SESSION['id_empresa'] ?? null;
$tipo = $_SESSION['tipo'] ?? null;

if (!$id_empresa || $tipo !== "empresa") {
    echo json_encode(["error" => "não autorizado"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT nome, email, codigo_acesso
    FROM empresa
    WHERE id_empresa = ?
");

$stmt->bind_param("i", $id_empresa);
$stmt->execute();

$result = $stmt->get_result();

echo json_encode($result->fetch_assoc());