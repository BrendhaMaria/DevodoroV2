<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function apiResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function requireAuth() {
    $tipo = $_SESSION['tipo'] ?? null;
    $id_empresa = $_SESSION['id_empresa'] ?? null;

    if (!$tipo || !$id_empresa || !is_numeric($id_empresa)) {
        apiResponse([
            "success" => false,
            "error" => "Usuario nao autenticado"
        ], 401);
    }

    if (!in_array($tipo, ["empresa", "funcionario"], true)) {
        apiResponse([
            "success" => false,
            "error" => "Sessao invalida"
        ], 401);
    }

    $auth = [
        "tipo" => $tipo,
        "id_empresa" => (int) $id_empresa,
        "id_funcionario" => null
    ];

    if ($tipo === "funcionario") {
        $id_funcionario = $_SESSION['id_funcionario'] ?? null;

        if (!$id_funcionario || !is_numeric($id_funcionario)) {
            apiResponse([
                "success" => false,
                "error" => "Sessao de funcionario invalida"
            ], 401);
        }

        $auth["id_funcionario"] = (int) $id_funcionario;
    }

    return $auth;
}

function requireEmpresa() {
    $auth = requireAuth();

    if ($auth["tipo"] !== "empresa") {
        apiResponse([
            "success" => false,
            "error" => "Acesso permitido apenas para empresa"
        ], 403);
    }

    return $auth;
}

function readJsonInput() {
    $raw = file_get_contents("php://input");

    if ($raw === false || trim($raw) === "") {
        return null;
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : null;
}
?>
