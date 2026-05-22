<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

require_once "../php/conexao.php";
require_once "../php/auth.php";
require_once "../php/tarefas.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $tarefas = listarTarefas($conn, $id_empresa);
    apiResponse($tarefas);
}

if ($method === "POST") {
    $data = readJsonInput();

    if (!$data) {
        apiResponse([
            "success" => false,
            "error" => "JSON invalido ou vazio"
        ], 400);
    }

    $titulo = $data["titulo"] ?? "";
    $estado = $data["estado"] ?? "PENDENTE";
    $prioridade = $data["prioridade"] ?? "MEDIA";
    $prazo_entrega = $data["prazo_entrega"] ?? null;

    $resultado = criarTarefa(
        $conn,
        $id_empresa,
        $titulo,
        $estado,
        $prioridade,
        $prazo_entrega
    );

    apiResponse($resultado, ($resultado["success"] ?? false) ? 201 : 400);
}

if ($method === "DELETE") {
    $data = readJsonInput();
    $id_tarefa = $data["id_tarefa"] ?? null;

    if (!$id_tarefa || !is_numeric($id_tarefa)) {
        apiResponse([
            "success" => false,
            "error" => "ID da tarefa invalido"
        ], 400);
    }

    $resultado = deletarTarefa($conn, $id_empresa, (int) $id_tarefa);
    apiResponse($resultado, ($resultado["success"] ?? false) ? 200 : 404);
}

if ($method === "PUT") {
    $data = readJsonInput();
    $id_tarefa = $data["id_tarefa"] ?? null;
    $estado = $data["estado"] ?? null;

    if (!$id_tarefa || !is_numeric($id_tarefa) || !$estado) {
        apiResponse([
            "success" => false,
            "error" => "Dados incompletos"
        ], 400);
    }

    $resultado = atualizarEstado($conn, $id_empresa, (int) $id_tarefa, $estado);
    apiResponse($resultado, ($resultado["success"] ?? false) ? 200 : 404);
}

apiResponse([
    "success" => false,
    "error" => "Metodo nao suportado",
    "method" => $method
], 405);
?>
