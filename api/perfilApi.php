<?php

session_start();

header("Content-Type: application/json");

include "../php/conexao.php";

/* =========================
   VERIFICA LOGIN
========================= */

if (!isset($_SESSION['tipo'])) {

    echo json_encode([
        "error" => "Usuário não autenticado"
    ]);

    exit;
}

$tipo = $_SESSION['tipo'];

/* =========================
   DEFINE TABELA
========================= */

if ($tipo === "empresa") {

    $tabela = "empresa";
    $campoId = "id_empresa";
    $id = $_SESSION['id_empresa'];

} else {

    $tabela = "funcionario";
    $campoId = "id_funcionario";
    $id = $_SESSION['id_funcionario'];
}

/* =========================
   GET → BUSCAR PERFIL
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "
        SELECT
            nome,
            email,
            foto_perfil
        FROM $tabela
        WHERE $campoId = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die(json_encode([
            "error" => $conn->error
        ]));
    }

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $result = $stmt->get_result();

    $usuario = $result->fetch_assoc();

    echo json_encode($usuario);

    exit;
}

/* =========================
   POST → ATUALIZAR PERFIL
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');

    if (!$nome) {

        echo json_encode([
            "error" => "Nome obrigatório"
        ]);

        exit;
    }

    $caminhoImagem = null;

    /* =========================
       UPLOAD FOTO
    ========================= */

    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] === 0
    ) {

        $arquivo = $_FILES['foto'];

        /* =========================
        LIMITE DE TAMANHO
        ========================= */

        $maxSize = 5 * 1024 * 1024;

        if ($arquivo["size"] > $maxSize) {

            echo json_encode([
                "error" => "Imagem muito grande. Máximo 5MB."
            ]);

            exit;
        }

        /* =========================
        MIME TYPE REAL
        ========================= */

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mime = finfo_file(
            $finfo,
            $arquivo["tmp_name"]
        );

        finfo_close($finfo);

        $mimesPermitidos = [
            "image/png" => "png",
            "image/jpeg" => "jpg"
        ];

        if (!isset($mimesPermitidos[$mime])) {

            echo json_encode([
                "error" => "Formato inválido."
            ]);

            exit;
        }

        /* =========================
        VERIFICA IMAGEM REAL
        ========================= */

        $imageInfo = getimagesize(
            $arquivo["tmp_name"]
        );

        if ($imageInfo === false) {

            echo json_encode([
                "error" => "Arquivo inválido."
            ]);

            exit;
        }

        /* =========================
        NOME ÚNICO SEGURO
        ========================= */

        $ext = $mimesPermitidos[$mime];

        $nomeArquivo =
            bin2hex(random_bytes(16))
            . "."
            . $ext;

        $diretorio = "../uploads/perfis/";

        if (!is_dir($diretorio)) {

            mkdir($diretorio, 0777, true);
        }

        $destino = $diretorio . $nomeArquivo;

        /* =========================
        BUSCA FOTO ANTIGA
        ========================= */

        $sqlFoto = "
            SELECT foto_perfil
            FROM $tabela
            WHERE $campoId = ?
        ";

        $stmtFoto = $conn->prepare($sqlFoto);

        $stmtFoto->bind_param("i", $id);

        $stmtFoto->execute();

        $resultFoto = $stmtFoto->get_result();

        $usuarioAtual = $resultFoto->fetch_assoc();

        $fotoAntiga =
            $usuarioAtual['foto_perfil'] ?? null;

        /* =========================
        MOVE ARQUIVO
        ========================= */

        if (!move_uploaded_file(
            $arquivo["tmp_name"],
            $destino
        )) {

            echo json_encode([
                "error" => "Erro ao salvar imagem."
            ]);

            exit;
        }

        /* =========================
        REMOVE FOTO ANTIGA
        ========================= */

        if (
            $fotoAntiga &&
            $fotoAntiga !== "uploads/perfis/default.png" &&
            file_exists("../" . $fotoAntiga)
        ) {

            unlink("../" . $fotoAntiga);
        }

        $caminhoImagem =
            "uploads/perfis/" . $nomeArquivo;
    }

    /* =========================
       UPDATE COM FOTO
    ========================= */

    if ($caminhoImagem) {

        $sql = "
            UPDATE $tabela
            SET
                nome = ?,
                foto_perfil = ?
            WHERE $campoId = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die(json_encode([
                "error" => $conn->error
            ]));
        }

        $stmt->bind_param(
            "ssi",
            $nome,
            $caminhoImagem,
            $id
        );

    }

    /* =========================
       UPDATE SEM FOTO
    ========================= */

    else {

        $sql = "
            UPDATE $tabela
            SET nome = ?
            WHERE $campoId = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die(json_encode([
                "error" => $conn->error
            ]));
        }

        $stmt->bind_param(
            "si",
            $nome,
            $id
        );
    }

    $stmt->execute();

    /* =========================
       ATUALIZA SESSÃO
    ========================= */

    $_SESSION['nome'] = $nome;

    echo json_encode([
        "success" => true,
        "message" => "Perfil atualizado"
    ]);

    exit;
}

/* =========================
   MÉTODO INVÁLIDO
========================= */

echo json_encode([
    "error" => "Método inválido"
]);