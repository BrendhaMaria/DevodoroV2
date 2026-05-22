<?php
header("Content-Type: application/json");

require_once "../php/conexao.php";
require_once "../php/auth.php";

$auth = requireAuth();

if ($auth["tipo"] === "empresa") {
    $tabela = "empresa";
    $campoId = "id_empresa";
    $id = $auth["id_empresa"];
} else {
    $tabela = "funcionario";
    $campoId = "id_funcionario";
    $id = $auth["id_funcionario"];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("
        SELECT nome, email, foto_perfil
        FROM $tabela
        WHERE $campoId = ?
    ");

    if (!$stmt) {
        apiResponse(["success" => false, "error" => $conn->error], 500);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $usuario = $stmt->get_result()->fetch_assoc();

    if (!$usuario) {
        apiResponse(["success" => false, "error" => "Usuario nao encontrado"], 404);
    }

    apiResponse($usuario);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '') {
        apiResponse(["success" => false, "error" => "Nome obrigatorio"], 400);
    }

    $caminhoImagem = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['foto'];
        $maxSize = 5 * 1024 * 1024;

        if ($arquivo["size"] > $maxSize) {
            apiResponse([
                "success" => false,
                "error" => "Imagem muito grande. Maximo 5MB."
            ], 400);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $arquivo["tmp_name"]);
        finfo_close($finfo);

        $mimesPermitidos = [
            "image/png" => "png",
            "image/jpeg" => "jpg"
        ];

        if (!isset($mimesPermitidos[$mime])) {
            apiResponse([
                "success" => false,
                "error" => "Formato invalido."
            ], 400);
        }

        if (getimagesize($arquivo["tmp_name"]) === false) {
            apiResponse([
                "success" => false,
                "error" => "Arquivo invalido."
            ], 400);
        }

        $ext = $mimesPermitidos[$mime];
        $nomeArquivo = bin2hex(random_bytes(16)) . "." . $ext;
        $diretorio = "../uploads/perfis/";

        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $destino = $diretorio . $nomeArquivo;

        $stmtFoto = $conn->prepare("
            SELECT foto_perfil
            FROM $tabela
            WHERE $campoId = ?
        ");

        if (!$stmtFoto) {
            apiResponse(["success" => false, "error" => $conn->error], 500);
        }

        $stmtFoto->bind_param("i", $id);
        $stmtFoto->execute();

        $usuarioAtual = $stmtFoto->get_result()->fetch_assoc();
        $fotoAntiga = $usuarioAtual['foto_perfil'] ?? null;

        if (!move_uploaded_file($arquivo["tmp_name"], $destino)) {
            apiResponse([
                "success" => false,
                "error" => "Erro ao salvar imagem."
            ], 500);
        }

        if (
            $fotoAntiga &&
            $fotoAntiga !== "uploads/perfis/default.png" &&
            file_exists("../" . $fotoAntiga)
        ) {
            unlink("../" . $fotoAntiga);
        }

        $caminhoImagem = "uploads/perfis/" . $nomeArquivo;
    }

    if ($caminhoImagem) {
        $stmt = $conn->prepare("
            UPDATE $tabela
            SET nome = ?, foto_perfil = ?
            WHERE $campoId = ?
        ");

        if (!$stmt) {
            apiResponse(["success" => false, "error" => $conn->error], 500);
        }

        $stmt->bind_param("ssi", $nome, $caminhoImagem, $id);
    } else {
        $stmt = $conn->prepare("
            UPDATE $tabela
            SET nome = ?
            WHERE $campoId = ?
        ");

        if (!$stmt) {
            apiResponse(["success" => false, "error" => $conn->error], 500);
        }

        $stmt->bind_param("si", $nome, $id);
    }

    if (!$stmt->execute()) {
        apiResponse(["success" => false, "error" => $stmt->error], 500);
    }

    $_SESSION['nome'] = $nome;

    apiResponse([
        "success" => true,
        "message" => "Perfil atualizado"
    ]);
}

apiResponse([
    "success" => false,
    "error" => "Metodo invalido"
], 405);
?>
