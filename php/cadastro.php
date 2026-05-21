<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexao.php";

$nome  = $_POST['nome'] ?? null;
$email = $_POST['email'] ?? null;
$senha = isset($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;
$tipo  = $_POST['tipo'] ?? null;

/* =========================
   VALIDAÇÃO BÁSICA
========================= */

if (!$nome || !$email || !$senha || !$tipo) {
    die("Dados incompletos");
}

/* =========================
   CADASTRO EMPRESA
========================= */

if ($tipo === "empresa") {

    // gera código único simples
    $codigo = substr(md5(uniqid()), 0, 8);

    $sql = "INSERT INTO empresa (nome, email, senha, codigo_acesso)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Erro SQL: " . $conn->error);

    $stmt->bind_param("ssss", $nome, $email, $senha, $codigo);

    if ($stmt->execute()) {
        echo "Empresa cadastrada. Código de acesso: " . $codigo;
    } else {
        echo "Erro: " . $stmt->error;
    }

}

/* =========================
   CADASTRO FUNCIONÁRIO
========================= */

else {

    $codigo = $_POST['codigo_empresa'] ?? null;

    if (!$codigo) {
        die("Código da empresa é obrigatório");
    }

    // busca empresa pelo código
    $sql = "SELECT id_empresa FROM empresa WHERE codigo_acesso = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) die("Erro SQL: " . $conn->error);

    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        die("Código da empresa inválido");
    }

    $empresa = $result->fetch_assoc();
    $id_empresa = $empresa['id_empresa'];

    // cadastra funcionário vinculado
    $sql = "INSERT INTO funcionario (nome, email, senha, id_empresa)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Erro SQL: " . $conn->error);

    $stmt->bind_param("sssi", $nome, $email, $senha, $id_empresa);

    if ($stmt->execute()) {
        echo "Funcionário cadastrado com sucesso";
    } else {
        echo "Erro: " . $stmt->error;
    }
}