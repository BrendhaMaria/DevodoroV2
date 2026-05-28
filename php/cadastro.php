<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: text/html; charset=utf-8");

require_once "conexao.php";

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senhaTexto = $_POST['senha'] ?? '';
$tipo = $_POST['tipo'] ?? '';

if ($nome === '' || $email === '' || $senhaTexto === '' || $tipo === '') {
    die("Dados incompletos");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email inválido");
}

if (!in_array($tipo, ["empresa", "funcionario"], true)) {
    die("Tipo de usuário inválido");
}

$senha = password_hash($senhaTexto, PASSWORD_DEFAULT);

if ($tipo === "empresa") {
    $codigo = null;

    for ($i = 0; $i < 5; $i++) {
        $codigoTentativa = bin2hex(random_bytes(4));

        $check = $conn->prepare("
            SELECT id_empresa
            FROM empresa
            WHERE codigo_acesso = ?
        ");

        if (!$check) {
            die("Erro SQL: " . $conn->error);
        }

        $check->bind_param("s", $codigoTentativa);
        $check->execute();

        if ($check->get_result()->num_rows === 0) {
            $codigo = $codigoTentativa;
            break;
        }
    }

if (!$codigo) {
        die("Não foi possível gerar código da empresa");
    }

    $stmt = $conn->prepare("
        INSERT INTO empresa (nome, email, senha, codigo_acesso)
        VALUES (?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Erro SQL: " . $conn->error);
    }

    $stmt->bind_param("ssss", $nome, $email, $senha, $codigo);

    if ($stmt->execute()) {
        echo "Empresa cadastrada. Código de acesso: " . htmlspecialchars($codigo, ENT_QUOTES, "UTF-8");
        exit;
    }

    die("Erro: " . $stmt->error);
}

$codigo = trim($_POST['codigo_empresa'] ?? '');

if ($codigo === '') {
    die("Código da empresa é obrigatório");
}

$stmt = $conn->prepare("
    SELECT id_empresa
    FROM empresa
    WHERE codigo_acesso = ?
    LIMIT 1
");

if (!$stmt) {
    die("Erro SQL: " . $conn->error);
}

$stmt->bind_param("s", $codigo);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Código da empresa inválido");
}

$empresa = $result->fetch_assoc();
$id_empresa = (int) $empresa['id_empresa'];

$stmt = $conn->prepare("
    INSERT INTO funcionario (nome, email, senha, id_empresa)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    die("Erro SQL: " . $conn->error);
}

$stmt->bind_param("sssi", $nome, $email, $senha, $id_empresa);

if ($stmt->execute()) {
    echo "Funcionário cadastrado com sucesso";
    exit;
}

die("Erro: " . $stmt->error);
?>
