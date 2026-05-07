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
   LISTAR EQUIPES
===================================================== */
if ($method === "GET") {

    $stmt = $conn->prepare("
        SELECT id, nome
        FROM equipe
        WHERE id_empresa = ?
    ");

    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();

    $result = $stmt->get_result();

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

/* =====================================================
   CRIAR EQUIPE
===================================================== */
if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $nome = $data["nome"] ?? null;

    if (!$nome) {
        echo json_encode(["error" => "nome obrigatório"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO equipe (nome, id_empresa)
        VALUES (?, ?)
    ");

    $stmt->bind_param("si", $nome, $id_empresa);
    $stmt->execute();

    echo json_encode(["status" => "ok"]);
    exit;
}

/* =====================================================
   REMOVER EQUIPE
===================================================== */
if ($method === "DELETE") {

    $id = $_GET["id"] ?? null;

    if (!$id) {
        echo json_encode(["error" => "id obrigatório"]);
        exit;
    }

    $stmt = $conn->prepare("
        DELETE FROM equipe
        WHERE id = ? AND id_empresa = ?
    ");

    $stmt->bind_param("ii", $id, $id_empresa);
    $stmt->execute();

    echo json_encode(["status" => "ok"]);
    exit;
}