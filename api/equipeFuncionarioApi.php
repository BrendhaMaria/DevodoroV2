<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$method = $_SERVER['REQUEST_METHOD'];

function equipePertenceEmpresa($conn, $id_equipe, $id_empresa) {
    $stmt = $conn->prepare("
        SELECT id
        FROM equipe
        WHERE id = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_equipe, $id_empresa);
    $stmt->execute();

    return $stmt->get_result()->num_rows === 1;
}

function funcionarioPertenceEmpresa($conn, $id_funcionario, $id_empresa) {
    $stmt = $conn->prepare("
        SELECT id_funcionario
        FROM funcionario
        WHERE id_funcionario = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_funcionario, $id_empresa);
    $stmt->execute();

    return $stmt->get_result()->num_rows === 1;
}

if ($method === "GET") {
    $id_equipe = $_GET["id_equipe"] ?? null;

    if (!$id_equipe || !is_numeric($id_equipe)) {
        apiResponse(["success" => false, "error" => "id_equipe obrigatório"], 400);
    }

    $id_equipe = (int) $id_equipe;

    if (!equipePertenceEmpresa($conn, $id_equipe, $id_empresa)) {
        apiResponse(["success" => false, "error" => "Equipe não encontrada"], 404);
    }

    $stmt = $conn->prepare("
        SELECT f.id_funcionario, f.nome, f.email
        FROM equipe_funcionario ef
        JOIN funcionario f ON f.id_funcionario = ef.id_funcionario
        JOIN equipe e ON e.id = ef.id_equipe
        WHERE ef.id_equipe = ?
          AND e.id_empresa = ?
          AND f.id_empresa = ?
        ORDER BY f.nome
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("iii", $id_equipe, $id_empresa, $id_empresa);
    $stmt->execute();

    $result = $stmt->get_result();
    apiResponse($result->fetch_all(MYSQLI_ASSOC));
}

if ($method === "POST") {
    $data = readJsonInput();
    $id_equipe = $data["id_equipe"] ?? null;
    $id_funcionario = $data["id_funcionario"] ?? null;

    if (
        !$id_equipe ||
        !$id_funcionario ||
        !is_numeric($id_equipe) ||
        !is_numeric($id_funcionario)
    ) {
        apiResponse(["success" => false, "error" => "Dados inválidos"], 400);
    }

    $id_equipe = (int) $id_equipe;
    $id_funcionario = (int) $id_funcionario;

    if (!equipePertenceEmpresa($conn, $id_equipe, $id_empresa)) {
        apiResponse(["success" => false, "error" => "Equipe invalida"], 404);
    }

    if (!funcionarioPertenceEmpresa($conn, $id_funcionario, $id_empresa)) {
        apiResponse(["success" => false, "error" => "Funcionário inválido"], 404);
    }

    $stmt = $conn->prepare("
        INSERT IGNORE INTO equipe_funcionario (id_equipe, id_funcionario)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("ii", $id_equipe, $id_funcionario);

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => $stmt->error], 500);
    }

    apiResponse(["success" => true], 201);
}

if ($method === "DELETE") {
    $id_equipe = $_GET["id_equipe"] ?? null;
    $id_funcionario = $_GET["id_funcionario"] ?? null;

    if (
        !$id_equipe ||
        !$id_funcionario ||
        !is_numeric($id_equipe) ||
        !is_numeric($id_funcionario)
    ) {
        apiResponse(["success" => false, "error" => "Dados inválidos"], 400);
    }

    $id_equipe = (int) $id_equipe;
    $id_funcionario = (int) $id_funcionario;

    $stmt = $conn->prepare("
        DELETE ef FROM equipe_funcionario ef
        JOIN equipe e ON e.id = ef.id_equipe
        JOIN funcionario f ON f.id_funcionario = ef.id_funcionario
        WHERE ef.id_equipe = ?
          AND ef.id_funcionario = ?
          AND e.id_empresa = ?
          AND f.id_empresa = ?
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("iiii", $id_equipe, $id_funcionario, $id_empresa, $id_empresa);

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => $stmt->error], 500);
    }

    apiResponse([
        "success" => $stmt->affected_rows > 0,
        "affected_rows" => $stmt->affected_rows
    ], $stmt->affected_rows > 0 ? 200 : 404);
}

apiResponse(["success" => false, "error" => "Método não suportado"], 405);
?>
