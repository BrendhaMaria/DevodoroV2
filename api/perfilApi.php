<?php
header("Content-Type: application/json; charset=utf-8");

require_once "../php/conexao.php";
require_once "../php/auth.php";

const AVATAR_PADRAO = "uploads/perfis/default.png";
const AVATAR_MAX_BYTES = 5242880;

function contextoUsuarioAutenticado() {
    $auth = requireAuth();

    if ($auth["tipo"] === "empresa") {
        return [
            "tabela" => "empresa",
            "campoId" => "id_empresa",
            "id" => $auth["id_empresa"],
            "tipo" => "empresa"
        ];
    }

    return [
        "tabela" => "funcionario",
        "campoId" => "id_funcionario",
        "id" => $auth["id_funcionario"],
        "tipo" => "funcionario"
    ];
}

function buscarPerfil($conn, $ctx) {
    $stmt = $conn->prepare("
        SELECT nome, email, foto_perfil
        FROM {$ctx["tabela"]}
        WHERE {$ctx["campoId"]} = ?
        LIMIT 1
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => "Erro ao preparar consulta."], 500);
    }

    $stmt->bind_param("i", $ctx["id"]);

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => "Erro ao buscar perfil."], 500);
    }

    $perfil = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$perfil) {
        apiResponse(["success" => false, "error" => "Usuario nao encontrado."], 404);
    }

    return $perfil;
}

function emailJaUsado($conn, $ctx, $email) {
    $stmt = $conn->prepare("
        SELECT {$ctx["campoId"]}
        FROM {$ctx["tabela"]}
        WHERE email = ? AND {$ctx["campoId"]} <> ?
        LIMIT 1
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => "Erro ao validar email."], 500);
    }

    $stmt->bind_param("si", $email, $ctx["id"]);

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => "Erro ao validar email."], 500);
    }

    $existe = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $existe;
}

function validarUploadAvatar($arquivo) {
    if (!isset($arquivo) || $arquivo["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($arquivo["error"] !== UPLOAD_ERR_OK) {
        apiResponse(["success" => false, "error" => "Erro no envio da imagem."], 400);
    }

    if ($arquivo["size"] > AVATAR_MAX_BYTES) {
        apiResponse(["success" => false, "error" => "Imagem muito grande. Maximo 5MB."], 400);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $arquivo["tmp_name"]);
    finfo_close($finfo);

    $mimesPermitidos = [
        "image/png" => "png",
        "image/jpeg" => "jpg"
    ];

    if (!isset($mimesPermitidos[$mime]) || getimagesize($arquivo["tmp_name"]) === false) {
        apiResponse(["success" => false, "error" => "Formato de imagem invalido."], 400);
    }

    return $mimesPermitidos[$mime];
}

function salvarAvatar($arquivo, $ext) {
    if ($ext === null) {
        return null;
    }

    $diretorio = "../uploads/perfis/";

    if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true)) {
        apiResponse(["success" => false, "error" => "Erro ao preparar diretorio de upload."], 500);
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . "." . $ext;
    $destino = $diretorio . $nomeArquivo;

    if (!move_uploaded_file($arquivo["tmp_name"], $destino)) {
        apiResponse(["success" => false, "error" => "Erro ao salvar imagem."], 500);
    }

    return "uploads/perfis/" . $nomeArquivo;
}

function removerAvatarAntigo($fotoAntiga) {
    if (!$fotoAntiga || $fotoAntiga === AVATAR_PADRAO) {
        return;
    }

    $arquivo = realpath("../" . $fotoAntiga);
    $diretorioUploads = realpath("../uploads/perfis");

    if ($arquivo && $diretorioUploads && strpos($arquivo, $diretorioUploads) === 0 && is_file($arquivo)) {
        unlink($arquivo);
    }
}

$ctx = contextoUsuarioAutenticado();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    apiResponse(buscarPerfil($conn, $ctx));
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    apiResponse(["success" => false, "error" => "Metodo invalido."], 405);
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");

if ($nome === "") {
    apiResponse(["success" => false, "error" => "Nome obrigatorio."], 400);
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    apiResponse(["success" => false, "error" => "Email invalido."], 400);
}

if (emailJaUsado($conn, $ctx, $email)) {
    apiResponse(["success" => false, "error" => "Este email ja esta em uso."], 409);
}

$perfilAtual = buscarPerfil($conn, $ctx);
$extAvatar = validarUploadAvatar($_FILES["foto"] ?? null);
$novoAvatar = salvarAvatar($_FILES["foto"] ?? null, $extAvatar);

if ($novoAvatar) {
    $stmt = $conn->prepare("
        UPDATE {$ctx["tabela"]}
        SET nome = ?, email = ?, foto_perfil = ?
        WHERE {$ctx["campoId"]} = ?
    ");

    if (!$stmt) {
        unlink("../" . $novoAvatar);
        apiResponse(["success" => false, "error" => "Erro ao preparar atualizacao."], 500);
    }

    $stmt->bind_param("sssi", $nome, $email, $novoAvatar, $ctx["id"]);
} else {
    $stmt = $conn->prepare("
        UPDATE {$ctx["tabela"]}
        SET nome = ?, email = ?
        WHERE {$ctx["campoId"]} = ?
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => "Erro ao preparar atualizacao."], 500);
    }

    $stmt->bind_param("ssi", $nome, $email, $ctx["id"]);
}

if (!$stmt->execute()) {
    if ($novoAvatar) {
        unlink("../" . $novoAvatar);
    }

    apiResponse(["success" => false, "error" => "Erro ao atualizar perfil."], 500);
}

$stmt->close();

if ($novoAvatar) {
    removerAvatarAntigo($perfilAtual["foto_perfil"] ?? null);
}

$_SESSION["nome"] = $nome;
$_SESSION["email"] = $email;

apiResponse([
    "success" => true,
    "message" => "Perfil atualizado.",
    "perfil" => buscarPerfil($conn, $ctx)
]);
?>
