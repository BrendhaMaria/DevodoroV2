<?php
include '../php/conexao.php';
session_start();

header("Content-Type: application/json");

$id_empresa = $_SESSION['id_empresa'];

if (!$id_empresa) {
    echo json_encode(["error" => "não autenticado"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

/* =========================
   ADICIONAR FUNCIONÁRIO NA EQUIPE
========================= */
if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id_equipe = $data["id_equipe"];
    $id_funcionario = $data["id_funcionario"];

    $stmt = $conn->prepare("
        INSERT INTO equipe_funcionario (id_equipe, id_funcionario)
        VALUES (?, ?)
    ");

    $stmt->bind_param("ii", $id_equipe, $id_funcionario);
    $stmt->execute();

    echo json_encode(["status" => "ok"]);
    exit;
}

/* =========================
   LISTAR FUNCIONÁRIOS DE UMA EQUIPE
========================= */
if ($method === "GET") {

    $id_equipe = $_GET["id_equipe"];

    $stmt = $conn->prepare("
        SELECT f.id_funcionario, f.nome, f.email
        FROM equipe_funcionario ef
        JOIN funcionario f ON ef.id_funcionario = f.id_funcionario
        WHERE ef.id_equipe = ?
    ");

    $stmt->bind_param("i", $id_equipe);
    $stmt->execute();

    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}