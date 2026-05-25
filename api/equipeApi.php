<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/auth.php";
require_once "../php/conexao.php";

$auth = requireAuth();
$idEmpresa = $auth["id_empresa"];
$method = $_SERVER["REQUEST_METHOD"];

function listarEquipes($conn, $idEmpresa) {
    $stmt = $conn->prepare("
        SELECT id, nome
        FROM equipe
        WHERE id_empresa = ?
        ORDER BY nome
    ");

    if (!$stmt) {
        apiError("Erro ao preparar consulta.", 500);
    }

    $stmt->bind_param("i", $idEmpresa);

    if (!$stmt->execute()) {
        apiError("Erro ao listar equipes.", 500);
    }

    $equipes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $equipes;
}

function criarEquipe($conn, $idEmpresa, $nome) {
    $stmt = $conn->prepare("
        INSERT INTO equipe (nome, id_empresa)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        apiError("Erro ao preparar cadastro.", 500);
    }

    $stmt->bind_param("si", $nome, $idEmpresa);

    if (!$stmt->execute()) {
        apiError("Erro ao criar equipe.", 500);
    }

    $id = $conn->insert_id;
    $stmt->close();

    return [
        "success" => true,
        "id" => $id,
        "nome" => $nome
    ];
}

function removerEquipe($conn, $idEmpresa, $idEquipe) {
    $stmt = $conn->prepare("
        DELETE FROM equipe
        WHERE id = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        apiError("Erro ao preparar remocao.", 500);
    }

    $stmt->bind_param("ii", $idEquipe, $idEmpresa);

    if (!$stmt->execute()) {
        apiError("Erro ao remover equipe.", 500);
    }

    $resultado = [
        "success" => $stmt->affected_rows > 0,
        "affected_rows" => $stmt->affected_rows
    ];

    $stmt->close();

    return $resultado;
}

if ($method === "GET") {
    apiResponse(listarEquipes($conn, $idEmpresa));
}

if ($method === "POST") {
    $data = readJsonInput();

    if (!$data) {
        apiError("JSON invalido ou vazio.", 400);
    }

    $nome = trim((string) ($data["nome"] ?? ""));

    if ($nome === "") {
        apiError("Nome obrigatorio.", 400);
    }

    apiResponse(criarEquipe($conn, $idEmpresa, $nome), 201);
}

if ($method === "DELETE") {
    $idEquipe = $_GET["id"] ?? null;

    if (!apiPositiveId($idEquipe)) {
        apiError("ID obrigatorio.", 400);
    }

    $resultado = removerEquipe($conn, $idEmpresa, (int) $idEquipe);
    apiResponse($resultado, $resultado["success"] ? 200 : 404);
}

apiError("Metodo nao suportado.", 405, ["method" => $method]);
?>
