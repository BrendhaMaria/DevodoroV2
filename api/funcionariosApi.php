<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/auth.php";
require_once "../php/conexao.php";

$auth = requireAuth();
$idEmpresa = $auth["id_empresa"];

apiRequireMethod("GET");

$stmt = $conn->prepare("
    SELECT id_funcionario, nome, email
    FROM funcionario
    WHERE id_empresa = ?
      AND ativo = 1
    ORDER BY nome
");

if (!$stmt) {
    apiError("Erro ao preparar consulta.", 500);
}

$stmt->bind_param("i", $idEmpresa);

if (!$stmt->execute()) {
    apiError("Erro ao listar funcionarios.", 500);
}

$funcionarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

apiResponse($funcionarios);
?>
