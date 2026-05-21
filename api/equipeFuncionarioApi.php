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

/* =====================================================
   LISTAR FUNCIONÁRIOS DE UMA EQUIPE
===================================================== */
if ($method === "GET") {

    $id_equipe = $_GET["id_equipe"] ?? null;

    if (!$id_equipe) {
        echo json_encode(["error" => "id_equipe obrigatório"]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT f.id_funcionario, f.nome, f.email
        FROM equipe_funcionario ef
        JOIN funcionario f ON f.id_funcionario = ef.id_funcionario
        JOIN equipe e ON e.id = ef.id_equipe
        WHERE ef.id_equipe = ? AND e.id_empresa = ?
    ");

    $stmt->bind_param("ii", $id_equipe, $id_empresa);
    $stmt->execute();

    $result = $stmt->get_result();

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

/* =====================================================
   ADICIONAR FUNCIONÁRIO NA EQUIPE
===================================================== */
if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id_equipe = $data["id_equipe"] ?? null;
    $id_funcionario = $data["id_funcionario"] ?? null;

    if (!$id_equipe || !$id_funcionario) {
        echo json_encode(["error" => "dados inválidos"]);
        exit;
    }

    // valida se equipe pertence à empresa
    $check = $conn->prepare("SELECT id FROM equipe WHERE id = ? AND id_empresa = ?");
    $check->bind_param("ii", $id_equipe, $id_empresa);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        echo json_encode(["error" => "equipe inválida"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO equipe_funcionario (id_equipe, id_funcionario)
        VALUES (?, ?)
    ");

    $stmt->bind_param("ii", $id_equipe, $id_funcionario);
    $stmt->execute();

    echo json_encode(["status" => "ok"]);
    exit;
}

/* =====================================================
   REMOVER FUNCIONÁRIO DA EQUIPE
===================================================== */
if ($method === "DELETE") {

    $id_equipe = $_GET["id_equipe"] ?? null;
    $id_funcionario = $_GET["id_funcionario"] ?? null;

    if (!$id_equipe || !$id_funcionario) {
        echo json_encode(["error" => "dados inválidos"]);
        exit;
    }

    $stmt = $conn->prepare("
        DELETE ef FROM equipe_funcionario ef
        JOIN equipe e ON e.id = ef.id_equipe
        WHERE ef.id_equipe = ? 
        AND ef.id_funcionario = ?
        AND e.id_empresa = ?
    ");

    $stmt->bind_param("iii", $id_equipe, $id_funcionario, $id_empresa);
    $stmt->execute();

    echo json_encode(["status" => "ok"]);
    exit;
}