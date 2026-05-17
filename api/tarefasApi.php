<?php
// ==========================
// DEBUG ATIVADO
// ==========================
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// Função padrão de resposta
function response($data, $estado = 200) {
    http_response_code($estado);
    echo json_encode($data);
    exit;
}

// Função de erro padrão
function errorResponse($message, $extra = []) {
    response([
        "success" => false,
        "error" => $message,
        "debug" => $extra
    ], 500);
}

// ==========================
// TESTE DE CARREGAMENTO
// ==========================
if (!file_exists("../php/conexao.php")) {
    errorResponse("conexao.php não encontrado");
}

if (!file_exists("../php/tarefas.php")) {
    errorResponse("tarefas.php não encontrado");
}

require_once "../php/conexao.php";
require_once "../php/tarefas.php";

// ==========================
// TESTE DE CONEXÃO
// ==========================
if (!isset($conn)) {
    errorResponse("Conexão não definida");
}

if ($conn->connect_error) {
    errorResponse("Erro no banco", $conn->connect_error);
}

// ==========================
// MÉTODO
// ==========================
$method = $_SERVER["REQUEST_METHOD"];

// ==========================
// LISTAR (GET)
// ==========================
if ($method === "GET") {
    try {
        $tarefas = listarTarefas($conn);
        response($tarefas);
    } catch (Exception $e) {
        errorResponse("Erro ao listar tarefas", $e->getMessage());
    }
}

// ==========================
// CRIAR (POST)
// ==========================
if ($method === "POST") {
    $raw = file_get_contents("php://input");

    if (!$raw) {
        errorResponse("Body vazio");
    }

    $data = json_decode($raw, true);

    if (!$data) {
        errorResponse("JSON inválido", $raw);
    }

    $titulo = $data["titulo"] ?? null;
    $estado = $data["estado"] ?? "PENDENTE";
    $prioridade = $data["prioridade"] ?? "MEDIA";
    $prazo_entrega = $data["prazo_entrega"] ?? null;

    if (!$titulo) {
        errorResponse("Titulo da tarefa vazio");
    }

    try {
        $resultado = criarTarefa($conn, $titulo, $estado, $prioridade, $prazo_entrega);
        response($resultado);
    } catch (Exception $e) {
        errorResponse("Erro ao criar tarefa", $e->getMessage());
    }
}

// ==========================
// DELETE
// ==========================
if ($method === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["id_tarefa"])) {
        errorResponse("ID não enviado");
    }

    try {
        $resultado = deletarTarefa($conn, $data["id_tarefa"]);
        response($resultado);
    } catch (Exception $e) {
        errorResponse("Erro ao deletar", $e->getMessage());
    }
}

// ==========================
// PUT
// ==========================
if ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["id_tarefa"]) || !isset($data["estado"])) {
        errorResponse("Dados incompletos");
    }

    try {
        $resultado = atualizarEstado($conn, $data["id_tarefa"], $data["estado"]);
        response($resultado);
    } catch (Exception $e) {
        errorResponse("Erro ao atualizar", $e->getMessage());
    }
}

// ==========================
// MÉTODO NÃO SUPORTADO
// ==========================
response([
    "success" => false,
    "error" => "Método não suportado",
    "method" => $method
], 405);
