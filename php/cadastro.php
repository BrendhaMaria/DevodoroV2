<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "conexao.php";

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senhaTexto = $_POST['senha'] ?? '';
$tipo = $_POST['tipo'] ?? '';

if ($nome === '' || $email === '' || $senhaTexto === '' || $tipo === '') {
    die("Dados incompletos");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email invalido");
}

if (!in_array($tipo, ["empresa", "funcionario"], true)) {
    die("Tipo de usuario invalido");
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
        die("Nao foi possivel gerar codigo da empresa");
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
        echo "Empresa cadastrada. Codigo de acesso: " . htmlspecialchars($codigo, ENT_QUOTES, "UTF-8");
        exit;
    }

    die("Erro: " . $stmt->error);
}

$codigo = trim($_POST['codigo_empresa'] ?? '');

if ($codigo === '') {
    die("Codigo da empresa e obrigatorio");
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
    die("Codigo da empresa invalido");
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
    echo "Funcionario cadastrado com sucesso";
    exit;
}

die("Erro: " . $stmt->error);
?>
