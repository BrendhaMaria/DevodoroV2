<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$method = $_SERVER['REQUEST_METHOD'];

function idValido($id) {
    return is_numeric($id) && (int) $id > 0;
}

function listarEquipes($conn, $id_empresa) {
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

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function criarEquipe($conn, $id_empresa, $nome) {
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

    return [
        "success" => true,
        "id" => $conn->insert_id,
        "nome" => $nome
    ];
}

function removerEquipe($conn, $id_empresa, $id) {
    $stmt = $conn->prepare("
        DELETE FROM equipe
        WHERE id = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("ii", $id, $id_empresa);

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => $stmt->error], 500);
    }

    return [
        "success" => $stmt->affected_rows > 0,
        "affected_rows" => $stmt->affected_rows
    ];
}

if ($method === "GET") {
    apiResponse(listarEquipes($conn, $id_empresa));
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

    apiResponse(criarEquipe($conn, $id_empresa, $nome), 201);
}

if ($method === "DELETE") {
    $id = $_GET["id"] ?? null;

    if (!idValido($id)) {
        apiResponse([
            "success" => false,
            "error" => "ID obrigatório"
        ], 400);
    }

    $resultado = removerEquipe($conn, $id_empresa, (int) $id);
    apiResponse($resultado, $resultado["success"] ? 200 : 404);
}

apiResponse([
    "success" => false,
    "error" => "Método não suportado"
], 405);
?>
