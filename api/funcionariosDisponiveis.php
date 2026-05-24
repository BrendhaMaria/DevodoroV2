<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";
require_once "../php/equipes.php";

$auth = requireAuth();
$idEmpresa = $auth["id_empresa"];
$method = $_SERVER["REQUEST_METHOD"];
$idEquipe = $_GET["id_equipe"] ?? null;

function listarFuncionariosDisponiveis($conn, $idEmpresa, $idEquipe) {
    $stmt = $conn->prepare("
        SELECT f.id_funcionario, f.nome, f.email
        FROM funcionario f
        WHERE f.id_empresa = ?
          AND f.ativo = 1
          AND NOT EXISTS (
              SELECT 1
              FROM equipe_funcionario ef
              WHERE ef.id_equipe = ?
                AND ef.id_funcionario = f.id_funcionario
          )
        ORDER BY f.nome
    ");

    if (!$stmt) {
        apiError("Erro ao preparar consulta.", 500);
    }

    $stmt->bind_param("ii", $idEmpresa, $idEquipe);

    if (!$stmt->execute()) {
        apiError("Erro ao listar funcionarios.", 500);
    }

    $funcionarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $funcionarios;
}

apiRequireMethod("GET");

if (!apiPositiveId($idEquipe)) {
    apiError("id_equipe obrigatorio.", 400);
}

$idEquipe = (int) $idEquipe;

if (!equipePertenceEmpresa($conn, $idEquipe, $idEmpresa)) {
    apiError("Equipe nao encontrada.", 404);
}

apiResponse(listarFuncionariosDisponiveis($conn, $idEmpresa, $idEquipe));
?>
