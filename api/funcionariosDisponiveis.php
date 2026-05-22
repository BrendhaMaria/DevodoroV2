<?php
header("Content-Type: application/json");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$id_equipe = $_GET['id_equipe'] ?? null;

if (!$id_equipe || !is_numeric($id_equipe)) {
    apiResponse(["success" => false, "error" => "id_equipe obrigatorio"], 400);
}

$id_equipe = (int) $id_equipe;

$check = $conn->prepare("
    SELECT id
    FROM equipe
    WHERE id = ? AND id_empresa = ?
");

if (!$check) {
    apiResponse(["success" => false, "error" => $conn->error], 500);
}

$check->bind_param("ii", $id_equipe, $id_empresa);
$check->execute();

if ($check->get_result()->num_rows !== 1) {
    apiResponse(["success" => false, "error" => "Equipe nao encontrada"], 404);
}

$stmt = $conn->prepare("
    SELECT id_funcionario, nome, email
    FROM funcionario
    WHERE id_empresa = ?
      AND ativo = 1
      AND id_funcionario NOT IN (
          SELECT id_funcionario
          FROM equipe_funcionario
          WHERE id_equipe = ?
      )
    ORDER BY nome
");

if (!$stmt) {
    apiResponse(["success" => false, "error" => $conn->error], 500);
}

$stmt->bind_param("ii", $id_empresa, $id_equipe);
$stmt->execute();

$result = $stmt->get_result();
apiResponse($result->fetch_all(MYSQLI_ASSOC));
?>
