<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexao.php";

$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
$tipo = $_POST['tipo'];

if ($tipo === "empresa") {

    $sql = "INSERT INTO empresa (nome, email, senha)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha);

} else {

    $sql = "INSERT INTO funcionario (nome, email, senha)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha);
}

if ($stmt->execute()) {
    echo "Cadastro realizado";
} else {
    echo "Erro: " . $stmt->error;
}