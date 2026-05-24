<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    apiResponse(["success" => false, "error" => "Metodo invalido."], 405);
}

$auth = requireEmpresa();
$idEmpresa = $auth["id_empresa"];

$stmt = $conn->prepare("
    SELECT nome, email, codigo_acesso
    FROM empresa
    WHERE id_empresa = ?
    LIMIT 1
");

if (!$stmt) {
    apiResponse(["success" => false, "error" => "Erro ao preparar consulta."], 500);
}

$stmt->bind_param("i", $idEmpresa);

if (!$stmt->execute()) {
    apiResponse(["success" => false, "error" => "Erro ao buscar empresa."], 500);
}

$empresa = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$empresa) {
    apiResponse(["success" => false, "error" => "Empresa nao encontrada."], 404);
}

apiResponse([
    "success" => true,
    "nome" => $empresa["nome"],
    "email" => $empresa["email"],
    "codigo_acesso" => $empresa["codigo_acesso"]
]);
?>
