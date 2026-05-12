<?php
include '../php/conexao.php';
session_start();

header("Content-Type: application/json");

$id_empresa = $_SESSION['id_empresa'];
$id_equipe = $_GET['id_equipe'] ?? null;

if (!$id_empresa || !$id_equipe) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id_funcionario, nome, email
    FROM funcionario
    WHERE id_empresa = ?
    AND id_funcionario NOT IN (
        SELECT id_funcionario
        FROM equipe_funcionario
        WHERE id_equipe = ?
    )
");

$stmt->bind_param("ii", $id_empresa, $id_equipe);
$stmt->execute();

$result = $stmt->get_result();

echo json_encode($result->fetch_all(MYSQLI_ASSOC));