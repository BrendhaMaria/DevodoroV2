<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$method = $_SERVER['REQUEST_METHOD'];
$id_equipe = $_GET['id_equipe'] ?? null;

function idValido($id) {
    return is_numeric($id) && (int) $id > 0;
}

function equipePertenceEmpresa($conn, $id_equipe, $id_empresa) {
    $stmt = $conn->prepare("
        SELECT id
        FROM equipe
        WHERE id = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("ii", $id_equipe, $id_empresa);
    $stmt->execute();

    return $stmt->get_result()->num_rows === 1;
}

function listarFuncionariosDisponiveis($conn, $id_empresa, $id_equipe) {
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
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("ii", $id_empresa, $id_equipe);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if ($method !== "GET") {
    apiResponse(["success" => false, "error" => "Método não suportado"], 405);
}

if (!idValido($id_equipe)) {
    apiResponse(["success" => false, "error" => "id_equipe obrigatório"], 400);
}

$id_equipe = (int) $id_equipe;

if (!equipePertenceEmpresa($conn, $id_equipe, $id_empresa)) {
    apiResponse(["success" => false, "error" => "Equipe não encontrada"], 404);
}

apiResponse(listarFuncionariosDisponiveis($conn, $id_empresa, $id_equipe));
?>
