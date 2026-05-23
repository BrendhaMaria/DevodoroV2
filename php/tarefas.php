<?php
require_once "conexao.php";

function tarefaIdValido($id_tarefa) {
    return is_int($id_tarefa) && $id_tarefa > 0;
}

function prazoValido($prazo_entrega) {
    if ($prazo_entrega === null || $prazo_entrega === "") {
        return true;
    }

    $data = DateTime::createFromFormat("Y-m-d", $prazo_entrega);

    return $data && $data->format("Y-m-d") === $prazo_entrega;
}

function listarTarefas($conn, $id_empresa) {
    $stmt = $conn->prepare("
        SELECT *
        FROM tarefas
        WHERE id_empresa = ?
        ORDER BY id_tarefa DESC
    ");

    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }

    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();

    $result = $stmt->get_result();
    $tarefas = [];

    while ($row = $result->fetch_assoc()) {
        $tarefas[] = $row;
    }

    return $tarefas;
}

function resumoTarefas($conn, $id_empresa) {
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN estado = 'CONCLUIDA' THEN 1 ELSE 0 END) AS concluidas,
            SUM(CASE WHEN estado = 'EM_ANDAMENTO' THEN 1 ELSE 0 END) AS em_progresso,
            SUM(
                CASE
                    WHEN prazo_entrega IS NOT NULL
                     AND prazo_entrega < CURDATE()
                     AND estado <> 'CONCLUIDA'
                    THEN 1
                    ELSE 0
                END
            ) AS atrasadas
        FROM tarefas
        WHERE id_empresa = ?
    ");

    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }

    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();

    $resumo = $stmt->get_result()->fetch_assoc();

    return [
        "success" => true,
        "total" => (int) ($resumo["total"] ?? 0),
        "concluidas" => (int) ($resumo["concluidas"] ?? 0),
        "em_progresso" => (int) ($resumo["em_progresso"] ?? 0),
        "atrasadas" => (int) ($resumo["atrasadas"] ?? 0)
    ];
}

function criarTarefa($conn, $id_empresa, $titulo, $estado, $prioridade, $prazo_entrega) {
    $titulo = trim((string) $titulo);

    if ($titulo === "") {
        return ["success" => false, "error" => "Título vazio"];
    }

    $estadosPermitidos = ["PENDENTE", "EM_ANDAMENTO", "CONCLUIDA"];
    $prioridadesPermitidas = ["BAIXA", "MEDIA", "ALTA"];

    if (!in_array($estado, $estadosPermitidos, true)) {
        return ["success" => false, "error" => "Estado inválido"];
    }

    if (!in_array($prioridade, $prioridadesPermitidas, true)) {
        return ["success" => false, "error" => "Prioridade invalida"];
    }

    if ($prazo_entrega === "") {
        $prazo_entrega = null;
    }

    if (!prazoValido($prazo_entrega)) {
        return ["success" => false, "error" => "Prazo inválido"];
    }

    $stmt = $conn->prepare("
        INSERT INTO tarefas (
            titulo,
            estado,
            prioridade,
            prazo_entrega,
            id_empresa
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }

    $stmt->bind_param(
        "ssssi",
        $titulo,
        $estado,
        $prioridade,
        $prazo_entrega,
        $id_empresa
    );

    if (!$stmt->execute()) {
        return ["success" => false, "error" => $stmt->error];
    }

    return [
        "success" => true,
        "id_tarefa" => $conn->insert_id
    ];
}

function deletarTarefa($conn, $id_empresa, $id_tarefa) {
    if (!tarefaIdValido($id_tarefa)) {
        return ["success" => false, "error" => "ID da tarefa inválido"];
    }

    $stmt = $conn->prepare("
        DELETE FROM tarefas
        WHERE id_tarefa = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }

    $stmt->bind_param("ii", $id_tarefa, $id_empresa);

    if (!$stmt->execute()) {
        return ["success" => false, "error" => $stmt->error];
    }

    return [
        "success" => $stmt->affected_rows > 0,
        "affected_rows" => $stmt->affected_rows
    ];
}

function atualizarEstado($conn, $id_empresa, $id_tarefa, $estado) {
    if (!tarefaIdValido($id_tarefa)) {
        return ["success" => false, "error" => "ID da tarefa inválido"];
    }

    $estadosPermitidos = ["PENDENTE", "EM_ANDAMENTO", "CONCLUIDA"];

    if (!in_array($estado, $estadosPermitidos, true)) {
        return ["success" => false, "error" => "Estado inválido"];
    }

    $stmt = $conn->prepare("
        UPDATE tarefas
        SET estado = ?
        WHERE id_tarefa = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }

    $stmt->bind_param("sii", $estado, $id_tarefa, $id_empresa);

    if (!$stmt->execute()) {
        return ["success" => false, "error" => $stmt->error];
    }

    if ($stmt->affected_rows === 0) {
        $check = $conn->prepare("
            SELECT id_tarefa
            FROM tarefas
            WHERE id_tarefa = ? AND id_empresa = ?
        ");

        if (!$check) {
            return ["success" => false, "error" => $conn->error];
        }

        $check->bind_param("ii", $id_tarefa, $id_empresa);
        $check->execute();

        if ($check->get_result()->num_rows !== 1) {
            return [
                "success" => false,
                "affected_rows" => 0
            ];
        }
    }

    return [
        "success" => true,
        "affected_rows" => $stmt->affected_rows
    ];
}
?>
