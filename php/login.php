<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
]);

session_start();

require_once "conexao.php";

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$tipo = $_POST['tipo'] ?? '';

if ($email === '' || $senha === '' || $tipo === '') {
    die("Dados incompletos");
}

if (!in_array($tipo, ["empresa", "funcionario"], true)) {
    die("Tipo de usuario invalido");
}

if ($tipo === "empresa") {
    $stmt = $conn->prepare("
        SELECT id_empresa, nome, senha
        FROM empresa
        WHERE email = ?
        LIMIT 1
    ");
} else {
    $stmt = $conn->prepare("
        SELECT id_funcionario, nome, senha, id_empresa
        FROM funcionario
        WHERE email = ? AND ativo = 1
        LIMIT 1
    ");
}

if (!$stmt) {
    die("Erro SQL: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Usuario nao encontrado");
}

$user = $result->fetch_assoc();

if (!password_verify($senha, $user['senha'])) {
    die("Senha incorreta");
}

if ($tipo === "funcionario" && empty($user['id_empresa'])) {
    die("Funcionario sem empresa vinculada");
}

session_regenerate_id(true);
$_SESSION = [];

$_SESSION['tipo'] = $tipo;
$_SESSION['nome'] = $user['nome'];

if ($tipo === "empresa") {
    $_SESSION['id_empresa'] = (int) $user['id_empresa'];
} else {
    $_SESSION['id_funcionario'] = (int) $user['id_funcionario'];
    $_SESSION['id_empresa'] = (int) $user['id_empresa'];
}

header("Location: ../html/dashboard/dashboard.html");
exit;
?>
