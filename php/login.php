<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexao.php";

$email = $_POST['email'] ?? null;
$senha = $_POST['senha'] ?? null;
$tipo  = $_POST['tipo'] ?? null;

if (!$email || !$senha || !$tipo) {
    die("Dados incompletos");
}

/* =========================
   EMPRESA
========================= */

if ($tipo === "empresa") {
    $sql = "SELECT * FROM empresa WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) die("Erro SQL: " . $conn->error);

    $stmt->bind_param("s", $email);
}

/* =========================
   FUNCIONÁRIO
========================= */

else {
    $sql = "SELECT * FROM funcionario WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) die("Erro SQL: " . $conn->error);

    $stmt->bind_param("s", $email);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Usuário não encontrado");
}

$user = $result->fetch_assoc();

/* =========================
   VERIFICA SENHA
========================= */

if (!password_verify($senha, $user['senha'])) {
    die("Senha incorreta");
}

/* =========================
   SESSÃO
========================= */

$_SESSION['tipo'] = $tipo;
$_SESSION['nome'] = $user['nome'];

if ($tipo === "empresa") {
    $_SESSION['id'] = $user['id_empresa'];

} else {
    $_SESSION['id'] = $user['id_funcionario'];
    $_SESSION['empresa'] = $user['id_empresa'] ?? null;
}

/* =========================
   REDIRECIONAMENTO
========================= */

header("Location: ../html/dashboard/dashboard.html");
exit;