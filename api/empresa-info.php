<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireEmpresa();
$id_empresa = $auth["id_empresa"];

$stmt = $conn->prepare("
    SELECT nome, email, codigo_acesso
    FROM empresa
    WHERE id_empresa = ?
");

if (!$stmt) {
    apiResponse(["success" => false, "error" => $conn->error], 500);
}

$stmt->bind_param("i", $id_empresa);
$stmt->execute();

$result = $stmt->get_result();
$empresa = $result->fetch_assoc();

if (!$empresa) {
    apiResponse(["success" => false, "error" => "Empresa não encontrada"], 404);
}

apiResponse($empresa);
?>
