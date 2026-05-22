<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    $stmt = $conn->prepare("
        SELECT id, nome
        FROM equipe
        WHERE id_empresa = ?
        ORDER BY nome
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();

    $result = $stmt->get_result();
    apiResponse($result->fetch_all(MYSQLI_ASSOC));
}

if ($method === "POST") {
    $data = readJsonInput();
    $nome = trim((string) ($data["nome"] ?? ""));

    if ($nome === "") {
        apiResponse([
            "success" => false,
            "error" => "Nome obrigatório"
        ], 400);
    }

    $stmt = $conn->prepare("
        INSERT INTO equipe (nome, id_empresa)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("si", $nome, $id_empresa);

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => $stmt->error], 500);
    }

    apiResponse([
        "success" => true,
        "id" => $conn->insert_id
    ], 201);
}

if ($method === "DELETE") {
    $id = $_GET["id"] ?? null;

    if (!$id || !is_numeric($id)) {
        apiResponse([
            "success" => false,
            "error" => "ID obrigatório"
        ], 400);
    }

    $stmt = $conn->prepare("
        DELETE FROM equipe
        WHERE id = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $id = (int) $id;
    $stmt->bind_param("ii", $id, $id_empresa);

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => $stmt->error], 500);
    }

    apiResponse([
        "success" => $stmt->affected_rows > 0,
        "affected_rows" => $stmt->affected_rows
    ], $stmt->affected_rows > 0 ? 200 : 404);
}

apiResponse([
    "success" => false,
    "error" => "Método não suportado"
], 405);
?>
