<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";
require_once "../php/tarefas.php";

$auth = requireAuth();
$id_empresa = $auth["id_empresa"];
$method = $_SERVER["REQUEST_METHOD"];

function responseFromResult($resultado, $successStatus = 200, $errorStatus = 400) {
    $success = $resultado["success"] ?? false;
    apiResponse($resultado, $success ? $successStatus : $errorStatus);
}

if ($method === "GET") {
    $tarefas = listarTarefas($conn, $id_empresa);

    if (isset($tarefas["success"]) && $tarefas["success"] === false) {
        apiResponse($tarefas, 500);
    }

    apiResponse($tarefas);
}

if ($method === "POST") {
    $data = readJsonInput();

    if (!$data) {
        apiResponse([
            "success" => false,
            "error" => "JSON inválido ou vazio"
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

    responseFromResult($resultado, 201, 400);
}

if ($method === "DELETE") {
    $data = readJsonInput();
    $id_tarefa = $data["id_tarefa"] ?? null;

    if (!$id_tarefa || !is_numeric($id_tarefa)) {
        apiResponse([
            "success" => false,
            "error" => "ID da tarefa inválido"
        ], 400);
    }

    $resultado = deletarTarefa($conn, $id_empresa, (int) $id_tarefa);
    responseFromResult($resultado, 200, 404);
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
    $status = isset($resultado["error"]) ? 400 : 404;
    responseFromResult($resultado, 200, $status);
}

apiResponse([
    "success" => false,
    "error" => "Método não suportado",
    "method" => $method
], 405);
?>
