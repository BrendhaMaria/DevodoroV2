<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "GET") {
    apiResponse([
        "success" => false,
        "error" => "Metodo nao suportado"
    ], 405);
}

$stmt = $conn->prepare("
    SELECT id_funcionario, nome, email
    FROM funcionario
    WHERE id_empresa = ?
      AND ativo = 1
    ORDER BY nome
");

if (!$stmt) {
    apiResponse(["success" => false, "error" => $conn->error], 500);
}

$stmt->bind_param("i", $id_empresa);
$stmt->execute();

apiResponse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
?>
