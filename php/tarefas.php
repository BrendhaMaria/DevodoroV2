<?php
require_once "conexao.php";

/**
 * Lista todas as tarefas
 */
function listarTarefas($conn) {
    $sql = "SELECT * FROM tarefas ORDER BY id DESC";
    $result = $conn->query($sql);

    $tarefas = [];

    while ($row = $result->fetch_assoc()) {
        $tarefas[] = $row;
    }

    return $tarefas;
}

/**
 * Cria uma nova tarefa
 */
function criarTarefa($conn, $text, $status) {
    if (empty($text)) {
        return ["success" => false, "error" => "Texto vazio"];
    }

    $stmt = $conn->prepare("INSERT INTO tarefas (text, status) VALUES (?, ?)");
    $stmt->bind_param("ss", $text, $status);

    $ok = $stmt->execute();

    return ["success" => $ok];
}

/**
 * Remove tarefa por ID
 */
function deletarTarefa($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM tarefas WHERE id = ?");
    $stmt->bind_param("i", $id);

    $ok = $stmt->execute();

    return ["success" => $ok];
}

/**
 * Atualiza status da tarefa
 */
function atualizarStatus($conn, $id, $status) {
    $stmt = $conn->prepare("UPDATE tarefas SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    $ok = $stmt->execute();

    return ["success" => $ok];
}
?>