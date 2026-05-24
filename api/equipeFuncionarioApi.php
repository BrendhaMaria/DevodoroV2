<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";
require_once "../php/equipes.php";

$auth = requireAuth();
$idEmpresa = $auth["id_empresa"];
$method = $_SERVER["REQUEST_METHOD"];

function listarMembrosEquipe($conn, $idEquipe, $idEmpresa) {
    $stmt = $conn->prepare("
        SELECT f.id_funcionario, f.nome, f.email
        FROM equipe_funcionario ef
        JOIN funcionario f ON f.id_funcionario = ef.id_funcionario
        JOIN equipe e ON e.id = ef.id_equipe
        WHERE ef.id_equipe = ?
          AND e.id_empresa = ?
          AND f.id_empresa = ?
          AND f.ativo = 1
        ORDER BY f.nome
    ");

    if (!$stmt) {
        apiError("Erro ao preparar consulta.", 500);
    }

    $stmt->bind_param("iii", $idEquipe, $idEmpresa, $idEmpresa);

    if (!$stmt->execute()) {
        apiError("Erro ao listar membros.", 500);
    }

    $membros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $membros;
}

function adicionarMembroEquipe($conn, $idEquipe, $idFuncionario) {
    $stmt = $conn->prepare("
        INSERT IGNORE INTO equipe_funcionario (id_equipe, id_funcionario)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        apiError("Erro ao preparar vinculo.", 500);
    }

    $stmt->bind_param("ii", $idEquipe, $idFuncionario);

    if (!$stmt->execute()) {
        apiError("Erro ao adicionar membro.", 500);
    }

    $resultado = [
        "success" => true,
        "affected_rows" => $stmt->affected_rows
    ];

    $stmt->close();

    return $resultado;
}

function removerMembroEquipe($conn, $idEquipe, $idFuncionario, $idEmpresa) {
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
        apiError("Erro ao preparar remocao.", 500);
    }

    $stmt->bind_param("iiii", $idEquipe, $idFuncionario, $idEmpresa, $idEmpresa);

    if (!$stmt->execute()) {
        apiError("Erro ao remover membro.", 500);
    }

    $resultado = [
        "success" => $stmt->affected_rows > 0,
        "affected_rows" => $stmt->affected_rows
    ];

    $stmt->close();

    return $resultado;
}

if ($method === "GET") {
    $idEquipe = $_GET["id_equipe"] ?? null;

    if (!apiPositiveId($idEquipe)) {
        apiError("id_equipe obrigatorio.", 400);
    }

    $idEquipe = (int) $idEquipe;

    if (!equipePertenceEmpresa($conn, $idEquipe, $idEmpresa)) {
        apiError("Equipe nao encontrada.", 404);
    }

    apiResponse(listarMembrosEquipe($conn, $idEquipe, $idEmpresa));
}

if ($method === "POST") {
    $data = readJsonInput();
    $idEquipe = $data["id_equipe"] ?? null;
    $idFuncionario = $data["id_funcionario"] ?? null;

    if (!apiPositiveId($idEquipe) || !apiPositiveId($idFuncionario)) {
        apiError("Dados invalidos.", 400);
    }

    $idEquipe = (int) $idEquipe;
    $idFuncionario = (int) $idFuncionario;

    if (!equipePertenceEmpresa($conn, $idEquipe, $idEmpresa)) {
        apiError("Equipe nao encontrada.", 404);
    }

    if (!funcionarioAtivoPertenceEmpresa($conn, $idFuncionario, $idEmpresa)) {
        apiError("Funcionario invalido.", 404);
    }

    $resultado = adicionarMembroEquipe($conn, $idEquipe, $idFuncionario);
    apiResponse($resultado, $resultado["affected_rows"] > 0 ? 201 : 200);
}

if ($method === "DELETE") {
    $idEquipe = $_GET["id_equipe"] ?? null;
    $idFuncionario = $_GET["id_funcionario"] ?? null;

    if (!apiPositiveId($idEquipe) || !apiPositiveId($idFuncionario)) {
        apiError("Dados invalidos.", 400);
    }

    $resultado = removerMembroEquipe(
        $conn,
        (int) $idEquipe,
        (int) $idFuncionario,
        $idEmpresa
    );

    apiResponse($resultado, $resultado["success"] ? 200 : 404);
}

apiError("Metodo nao suportado.", 405, ["method" => $method]);
?>
