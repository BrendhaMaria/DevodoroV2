<?php
require_once "conexao.php";
session_start();

/**
 * Lista tarefas da empresa logada
 */
function listarTarefas($conn) {
    $id_empresa = $_SESSION['id_empresa'];

    $stmt = $conn->prepare("SELECT * FROM tarefas WHERE id_empresa = ? ORDER BY id_tarefa DESC");

    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();

    $result = $stmt->get_result();

    $tarefas = [];

    while ($row = $result->fetch_assoc()) {
        $tarefas[] = $row;
    }

    return $tarefas;
}

/**
 * Cria uma nova tarefa
 */
function criarTarefa($conn, $titulo, $estado, $prioridade) {
    $id_empresa = $_SESSION['id_empresa'];

    if (empty($titulo)) {
        return ["success" => false, "error" => "Título vazio"];
    }

    $stmt = $conn->prepare("INSERT INTO tarefas (titulo, estado, prioridade, id_empresa) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $titulo, $estado, $prioridade, $id_empresa);

    return ["success" => $stmt->execute()];
}

/**
 * Remove tarefa por ID
 */
function deletarTarefa($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tarefas WHERE id_tarefa = ?");
    
    $stmt->bind_param("i", $id);

    return ["success" => $stmt->execute()];
}

/**
 * Atualiza estado da tarefa
 */
function atualizarStatus($conn, $id, $estado) {
    $stmt = $conn->prepare("UPDATE tarefas SET estado = ? WHERE id_tarefa = ?");
    
    $stmt->bind_param("si", $estado, $id);

    return ["success" => $stmt->execute()];
}
?>