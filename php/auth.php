<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function apiResponse($data, $status = 200) {
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=utf-8");
    }

    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function apiError($message, $status = 400, $extra = []) {
    apiResponse(array_merge([
        "success" => false,
        "error" => $message
    ], $extra), $status);
}

function apiSuccess($data = [], $status = 200) {
    apiResponse(array_merge(["success" => true], $data), $status);
}

function apiRequireMethod($method) {
    if ($_SERVER["REQUEST_METHOD"] !== $method) {
        apiError("Metodo nao suportado.", 405, [
            "method" => $_SERVER["REQUEST_METHOD"]
        ]);
    }
}

function apiPositiveId($id) {
    return is_numeric($id) && (int) $id > 0;
}

function requireAuth() {
    $tipo = $_SESSION["tipo"] ?? null;
    $idEmpresa = $_SESSION["id_empresa"] ?? null;

    if (!$tipo || !$idEmpresa || !is_numeric($idEmpresa)) {
        apiError("Usuario nao autenticado.", 401);
    }

    if (!in_array($tipo, ["empresa", "funcionario"], true)) {
        apiError("Sessao invalida.", 401);
    }

    $auth = [
        "tipo" => $tipo,
        "id_empresa" => (int) $idEmpresa,
        "id_funcionario" => null
    ];

    if ($tipo === "funcionario") {
        $idFuncionario = $_SESSION["id_funcionario"] ?? null;

        if (!$idFuncionario || !is_numeric($idFuncionario)) {
            apiError("Sessao de funcionario invalida.", 401);
        }

        $auth["id_funcionario"] = (int) $idFuncionario;
    }

    return $auth;
}

function requireEmpresa() {
    $auth = requireAuth();

    if ($auth["tipo"] !== "empresa") {
        apiError("Acesso permitido apenas para empresa.", 403);
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
