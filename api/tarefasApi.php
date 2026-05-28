<?php
error_reporting(E_ALL);
ini_set("display_errors", 0);

header("Content-Type: application/json; charset=utf-8");

require_once "../php/auth.php";
require_once "../php/conexao.php";
require_once "../php/tarefas.php";

$auth = requireAuth();
$idEmpresa = $auth["id_empresa"];
$method = $_SERVER["REQUEST_METHOD"];

function responderResultado($resultado, $successStatus = 200, $errorStatus = 400) {
    $success = $resultado["success"] ?? false;
    apiResponse($resultado, $success ? $successStatus : $errorStatus);
}

if ($method === "GET") {
    if (($_GET["resumo"] ?? "") === "1") {
        $resumo = resumoTarefas($conn, $idEmpresa);
        responderResultado($resumo, 200, 500);
    }

    $tarefas = listarTarefas($conn, $idEmpresa);

    if (($tarefas["success"] ?? true) === false) {
        apiResponse($tarefas, 500);
    }

    apiResponse($tarefas);
}

if ($method === "POST") {
    $data = readJsonInput();

    if (!$data) {
        apiError("JSON invalido ou vazio.", 400);
    }

    $resultado = criarTarefa(
        $conn,
        $idEmpresa,
        $data["titulo"] ?? "",
        $data["estado"] ?? "PENDENTE",
        $data["prioridade"] ?? "MEDIA",
        $data["prazo_entrega"] ?? null,
        $data["id_equipes"] ?? [],
        $data["id_funcionarios"] ?? []
    );

    responderResultado($resultado, 201, 400);
}

if ($method === "DELETE") {
    $data = readJsonInput();

    if (!$data) {
        apiError("JSON invalido ou vazio.", 400);
    }

    $idTarefa = $data["id_tarefa"] ?? null;

    if (!apiPositiveId($idTarefa)) {
        apiError("ID da tarefa invalido.", 400);
    }

    $resultado = deletarTarefa($conn, $idEmpresa, (int) $idTarefa);
    responderResultado($resultado, 200, 404);
}

if ($method === "PUT") {
    $data = readJsonInput();

    if (!$data) {
        apiError("JSON invalido ou vazio.", 400);
    }

    $idTarefa = $data["id_tarefa"] ?? null;
    $estado = $data["estado"] ?? null;
    $acao = $data["acao"] ?? null;

    if (!apiPositiveId($idTarefa)) {
        apiError("ID da tarefa invalido.", 400);
    }

    if ($acao === "vinculos" || array_key_exists("id_equipes", $data) || array_key_exists("id_funcionarios", $data)) {
        $resultado = atualizarVinculosTarefa(
            $conn,
            $idEmpresa,
            (int) $idTarefa,
            $data["id_equipes"] ?? [],
            $data["id_funcionarios"] ?? []
        );

        $status = ($resultado["error"] ?? "") === "Tarefa nao encontrada" ? 404 : 400;
        responderResultado($resultado, 200, $status);
    }

    if (!$estado) {
        apiError("Dados incompletos.", 400);
    }

    $resultado = atualizarEstado($conn, $idEmpresa, (int) $idTarefa, $estado);
    $status = isset($resultado["error"]) ? 400 : 404;
    responderResultado($resultado, 200, $status);
}

apiError("Metodo nao suportado.", 405, ["method" => $method]);
?>
