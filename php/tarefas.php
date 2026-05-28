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

function normalizarIds($ids) {
    if (!is_array($ids)) {
        return [];
    }

    $normalizados = [];

    foreach ($ids as $id) {
        if (is_numeric($id) && (int) $id > 0) {
            $normalizados[] = (int) $id;
        }
    }

    return array_values(array_unique($normalizados));
}

function validarEquipesDaEmpresa($conn, $id_empresa, $ids_equipes) {
    if (count($ids_equipes) === 0) {
        return ["success" => true];
    }

    $placeholders = implode(",", array_fill(0, count($ids_equipes), "?"));
    $types = str_repeat("i", count($ids_equipes)) . "i";
    $params = array_merge($ids_equipes, [$id_empresa]);

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM equipe
        WHERE id IN ($placeholders)
          AND id_empresa = ?
    ");

    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ((int) ($row["total"] ?? 0) !== count($ids_equipes)) {
        return ["success" => false, "error" => "Equipe invalida para esta empresa"];
    }

    return ["success" => true];
}

function validarFuncionariosDaEmpresa($conn, $id_empresa, $ids_funcionarios) {
    if (count($ids_funcionarios) === 0) {
        return ["success" => true];
    }

    $placeholders = implode(",", array_fill(0, count($ids_funcionarios), "?"));
    $types = str_repeat("i", count($ids_funcionarios)) . "i";
    $params = array_merge($ids_funcionarios, [$id_empresa]);

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM funcionario
        WHERE id_funcionario IN ($placeholders)
          AND id_empresa = ?
          AND ativo = 1
    ");

    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ((int) ($row["total"] ?? 0) !== count($ids_funcionarios)) {
        return ["success" => false, "error" => "Funcionario invalido para esta empresa"];
    }

    return ["success" => true];
}

function tarefaPertenceEmpresa($conn, $id_empresa, $id_tarefa) {
    $stmt = $conn->prepare("
        SELECT id_tarefa
        FROM tarefas
        WHERE id_tarefa = ? AND id_empresa = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_tarefa, $id_empresa);
    $stmt->execute();

    return $stmt->get_result()->num_rows === 1;
}

function salvarVinculosTarefa($conn, $id_tarefa, $ids_equipes, $ids_funcionarios) {
    if (count($ids_equipes) > 0) {
        $stmtEquipe = $conn->prepare("
            INSERT INTO tarefa_equipe (id_tarefa, id_equipe)
            VALUES (?, ?)
        ");

        if (!$stmtEquipe) {
            return ["success" => false, "error" => $conn->error];
        }

        foreach ($ids_equipes as $id_equipe) {
            $stmtEquipe->bind_param("ii", $id_tarefa, $id_equipe);

            if (!$stmtEquipe->execute()) {
                return ["success" => false, "error" => $stmtEquipe->error];
            }
        }
    }

    if (count($ids_funcionarios) > 0) {
        $stmtFuncionario = $conn->prepare("
            INSERT INTO tarefa_funcionario (id_tarefa, id_funcionario)
            VALUES (?, ?)
        ");

        if (!$stmtFuncionario) {
            return ["success" => false, "error" => $conn->error];
        }

        foreach ($ids_funcionarios as $id_funcionario) {
            $stmtFuncionario->bind_param("ii", $id_tarefa, $id_funcionario);

            if (!$stmtFuncionario->execute()) {
                return ["success" => false, "error" => $stmtFuncionario->error];
            }
        }
    }

    return ["success" => true];
}

function substituirVinculosTarefa($conn, $id_tarefa, $ids_equipes, $ids_funcionarios) {
    $deleteEquipes = $conn->prepare("DELETE FROM tarefa_equipe WHERE id_tarefa = ?");

    if (!$deleteEquipes) {
        return ["success" => false, "error" => $conn->error];
    }

    $deleteEquipes->bind_param("i", $id_tarefa);

    if (!$deleteEquipes->execute()) {
        return ["success" => false, "error" => $deleteEquipes->error];
    }

    $deleteFuncionarios = $conn->prepare("DELETE FROM tarefa_funcionario WHERE id_tarefa = ?");

    if (!$deleteFuncionarios) {
        return ["success" => false, "error" => $conn->error];
    }

    $deleteFuncionarios->bind_param("i", $id_tarefa);

    if (!$deleteFuncionarios->execute()) {
        return ["success" => false, "error" => $deleteFuncionarios->error];
    }

    return salvarVinculosTarefa($conn, $id_tarefa, $ids_equipes, $ids_funcionarios);
}

function anexarVinculosTarefas($conn, $tarefas, $id_empresa) {
    if (count($tarefas) === 0) {
        return $tarefas;
    }

    $mapa = [];
    $ids = [];

    foreach ($tarefas as $index => $tarefa) {
        $id = (int) $tarefa["id_tarefa"];
        $ids[] = $id;
        $mapa[$id] = $index;
        $tarefas[$index]["equipes"] = [];
        $tarefas[$index]["funcionarios"] = [];
    }

    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $types = str_repeat("i", count($ids)) . "i";

    $stmtEquipes = $conn->prepare("
        SELECT te.id_tarefa, e.id, e.nome
        FROM tarefa_equipe te
        JOIN equipe e ON e.id = te.id_equipe
        WHERE te.id_tarefa IN ($placeholders)
          AND e.id_empresa = ?
        ORDER BY e.nome
    ");

    if (!$stmtEquipes) {
        return ["success" => false, "error" => $conn->error];
    }

    $paramsEquipes = array_merge($ids, [$id_empresa]);
    $stmtEquipes->bind_param($types, ...$paramsEquipes);
    $stmtEquipes->execute();
    $resultEquipes = $stmtEquipes->get_result();

    while ($row = $resultEquipes->fetch_assoc()) {
        $id_tarefa = (int) $row["id_tarefa"];

        if (isset($mapa[$id_tarefa])) {
            $tarefas[$mapa[$id_tarefa]]["equipes"][] = [
                "id" => (int) $row["id"],
                "nome" => $row["nome"]
            ];
        }
    }

    $stmtFuncionarios = $conn->prepare("
        SELECT tf.id_tarefa, f.id_funcionario, f.nome, f.email
        FROM tarefa_funcionario tf
        JOIN funcionario f ON f.id_funcionario = tf.id_funcionario
        WHERE tf.id_tarefa IN ($placeholders)
          AND f.id_empresa = ?
        ORDER BY f.nome
    ");

    if (!$stmtFuncionarios) {
        return ["success" => false, "error" => $conn->error];
    }

    $paramsFuncionarios = array_merge($ids, [$id_empresa]);
    $stmtFuncionarios->bind_param($types, ...$paramsFuncionarios);
    $stmtFuncionarios->execute();
    $resultFuncionarios = $stmtFuncionarios->get_result();

    while ($row = $resultFuncionarios->fetch_assoc()) {
        $id_tarefa = (int) $row["id_tarefa"];

        if (isset($mapa[$id_tarefa])) {
            $tarefas[$mapa[$id_tarefa]]["funcionarios"][] = [
                "id_funcionario" => (int) $row["id_funcionario"],
                "nome" => $row["nome"],
                "email" => $row["email"]
            ];
        }
    }

    return $tarefas;
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

    return anexarVinculosTarefas($conn, $tarefas, $id_empresa);
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

function criarTarefa($conn, $id_empresa, $titulo, $estado, $prioridade, $prazo_entrega, $ids_equipes = [], $ids_funcionarios = []) {
    $titulo = trim((string) $titulo);
    $ids_equipes = normalizarIds($ids_equipes);
    $ids_funcionarios = normalizarIds($ids_funcionarios);

    if ($titulo === "") {
        return ["success" => false, "error" => "Titulo vazio"];
    }

    $estadosPermitidos = ["PENDENTE", "EM_ANDAMENTO", "CONCLUIDA"];
    $prioridadesPermitidas = ["BAIXA", "MEDIA", "ALTA"];

    if (!in_array($estado, $estadosPermitidos, true)) {
        return ["success" => false, "error" => "Estado invalido"];
    }

    if (!in_array($prioridade, $prioridadesPermitidas, true)) {
        return ["success" => false, "error" => "Prioridade invalida"];
    }

    if ($prazo_entrega === "") {
        $prazo_entrega = null;
    }

    if (!prazoValido($prazo_entrega)) {
        return ["success" => false, "error" => "Prazo invalido"];
    }

    $validacaoEquipes = validarEquipesDaEmpresa($conn, $id_empresa, $ids_equipes);

    if (!$validacaoEquipes["success"]) {
        return $validacaoEquipes;
    }

    $validacaoFuncionarios = validarFuncionariosDaEmpresa($conn, $id_empresa, $ids_funcionarios);

    if (!$validacaoFuncionarios["success"]) {
        return $validacaoFuncionarios;
    }

    $conn->begin_transaction();

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
        $conn->rollback();
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
        $conn->rollback();
        return ["success" => false, "error" => $stmt->error];
    }

    $id_tarefa = $conn->insert_id;
    $vinculos = salvarVinculosTarefa($conn, $id_tarefa, $ids_equipes, $ids_funcionarios);

    if (!$vinculos["success"]) {
        $conn->rollback();
        return $vinculos;
    }

    $conn->commit();

    return [
        "success" => true,
        "id_tarefa" => $id_tarefa
    ];
}

function atualizarVinculosTarefa($conn, $id_empresa, $id_tarefa, $ids_equipes = [], $ids_funcionarios = []) {
    if (!tarefaIdValido($id_tarefa)) {
        return ["success" => false, "error" => "ID da tarefa invalido"];
    }

    if (!tarefaPertenceEmpresa($conn, $id_empresa, $id_tarefa)) {
        return ["success" => false, "error" => "Tarefa nao encontrada"];
    }

    $ids_equipes = normalizarIds($ids_equipes);
    $ids_funcionarios = normalizarIds($ids_funcionarios);

    $validacaoEquipes = validarEquipesDaEmpresa($conn, $id_empresa, $ids_equipes);

    if (!$validacaoEquipes["success"]) {
        return $validacaoEquipes;
    }

    $validacaoFuncionarios = validarFuncionariosDaEmpresa($conn, $id_empresa, $ids_funcionarios);

    if (!$validacaoFuncionarios["success"]) {
        return $validacaoFuncionarios;
    }

    $conn->begin_transaction();
    $resultado = substituirVinculosTarefa($conn, $id_tarefa, $ids_equipes, $ids_funcionarios);

    if (!$resultado["success"]) {
        $conn->rollback();
        return $resultado;
    }

    $conn->commit();

    return ["success" => true];
}

function deletarTarefa($conn, $id_empresa, $id_tarefa) {
    if (!tarefaIdValido($id_tarefa)) {
        return ["success" => false, "error" => "ID da tarefa invalido"];
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
        return ["success" => false, "error" => "ID da tarefa invalido"];
    }

    $estadosPermitidos = ["PENDENTE", "EM_ANDAMENTO", "CONCLUIDA"];

    if (!in_array($estado, $estadosPermitidos, true)) {
        return ["success" => false, "error" => "Estado invalido"];
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
