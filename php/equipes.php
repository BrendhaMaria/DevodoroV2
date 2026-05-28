<?php
function equipePertenceEmpresa($conn, $idEquipe, $idEmpresa) {
    $stmt = $conn->prepare("
        SELECT id
        FROM equipe
        WHERE id = ? AND id_empresa = ?
        LIMIT 1
    ");

    if (!$stmt) {
        apiError("Erro ao preparar consulta.", 500);
    }

    $stmt->bind_param("ii", $idEquipe, $idEmpresa);

    if (!$stmt->execute()) {
        apiError("Erro ao buscar equipe.", 500);
    }

    $existe = $stmt->get_result()->num_rows === 1;
    $stmt->close();

    return $existe;
}

function funcionarioAtivoPertenceEmpresa($conn, $idFuncionario, $idEmpresa) {
    $stmt = $conn->prepare("
        SELECT id_funcionario
        FROM funcionario
        WHERE id_funcionario = ?
          AND id_empresa = ?
          AND ativo = 1
        LIMIT 1
    ");

    if (!$stmt) {
        apiError("Erro ao preparar consulta.", 500);
    }

    $stmt->bind_param("ii", $idFuncionario, $idEmpresa);

    if (!$stmt->execute()) {
        apiError("Erro ao buscar funcionario.", 500);
    }

    $existe = $stmt->get_result()->num_rows === 1;
    $stmt->close();

    return $existe;
}
?>
