<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexao.php";

$email = $_POST['email'] ?? null;
$senha = $_POST['senha'] ?? null;
$tipo  = $_POST['tipo'] ?? null;

/* =========================
   VALIDAÇÃO
========================= */

if (!$email || !$senha || !$tipo) {
    die("Dados incompletos");
}

/* =========================
   EMPRESA
========================= */

if ($tipo === "empresa") {

    $sql = "SELECT id_empresa, nome, senha FROM empresa WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) die("Erro SQL: " . $conn->error);

    $stmt->bind_param("s", $email);
}

/* =========================
   FUNCIONÁRIO
========================= */

else {

    $sql = "SELECT id_funcionario, nome, senha, id_empresa 
            FROM funcionario 
            WHERE email = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) die("Erro SQL: " . $conn->error);

    $stmt->bind_param("s", $email);
}

$stmt->execute();
$result = $stmt->get_result();

/* =========================
   USUÁRIO EXISTE?
========================= */

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

/* =========================
   DIFERENCIAÇÃO
========================= */

if ($tipo === "empresa") {

    $_SESSION['id_empresa'] = $user['id_empresa'];

} else {

    $_SESSION['id_funcionario'] = $user['id_funcionario'];
    $_SESSION['id_empresa']     = $user['id_empresa'];

    // proteção básica (não deveria acontecer, mas evita bug)
    if (!$user['id_empresa']) {
        die("Funcionário sem empresa vinculada");
    }
}

/* =========================
   REDIRECIONAMENTO
========================= */

header("Location: ../html/dashboard/dashboard.html");
exit;