<?php
session_start();

$url_api = "http://localhost/redesocial/back/api.php";

function chamarApi($url, $metodo, $dados = null) {
    $opcoes = [
        "http" => [
            "method" => $metodo,
            "header" => "Content-Type: application/json\r\n",
            "ignore_errors" => true
        ]
    ];
    if ($dados !== null) {
        $opcoes["http"]["content"] = json_encode($dados);
    }
    $contexto = stream_context_create($opcoes);
    $resposta = @file_get_contents($url, false, $contexto);

    if ($resposta === false) {
        return ["_falha_conexao" => true];
    }

    $json = json_decode($resposta, true);
    return is_array($json) ? $json : ["_resposta_invalida" => $resposta];
}

$acao = $_POST["acao"] ?? $_GET["acao"] ?? "";

// 1. CADASTRO -> Remove "@" extra se o usuário digitou e salva no MySQL
if ($acao === "cadastro") {
    $nome = trim($_POST["nome"] ?? "");
    $username = ltrim(trim($_POST["username"] ?? ""), "@");
    $email = trim($_POST["email"] ?? "");
    $senha = trim($_POST["senha"] ?? "");

    if (strlen($senha) < 4) {
        header("Location: cadastro.php?erro=" . urlencode("A senha precisa ter pelo menos 4 caracteres."));
        exit;
    }

    $existentes = chamarApi($url_api . "?rota=usuario", "GET");
    if (isset($existentes["_falha_conexao"])) {
        header("Location: cadastro.php?erro=" . urlencode("Não foi possível conectar ao servidor da API."));
        exit;
    }

    if (is_array($existentes) && !isset($existentes["_resposta_invalida"])) {
        foreach ($existentes as $u) {
            if (isset($u["email"]) && strtolower($u["email"]) === strtolower($email)) {
                header("Location: cadastro.php?erro=" . urlencode("Este e-mail já está cadastrado. Tente fazer login."));
                exit;
            }
            if (isset($u["username"]) && strtolower(ltrim($u["username"], "@")) === strtolower($username)) {
                header("Location: cadastro.php?erro=" . urlencode("Este @username já está em uso. Escolha outro."));
                exit;
            }
        }
    }

    $res = chamarApi($url_api . "?rota=usuario", "POST", [
        "nome" => $nome,
        "username" => $username,
        "email" => $email,
        "senha" => $senha
    ]);

    if (isset($res["id"])) {
        header("Location: index.php?sucesso=" . urlencode("Conta criada com sucesso! Entre com seu e-mail e senha."));
    } else {
        $msg = $res["erro"] ?? "Erro ao salvar no banco.";
        header("Location: cadastro.php?erro=" . urlencode($msg));
    }
    exit;
}

// 2. LOGIN
if ($acao === "login") {
    $email = trim($_POST["email"] ?? "");
    $senha = trim($_POST["senha"] ?? "");

    $usuarios = chamarApi($url_api . "?rota=usuario", "GET");

    if (isset($usuarios["_falha_conexao"]) || isset($usuarios["_resposta_invalida"])) {
        header("Location: index.php?erro=" . urlencode("Erro de comunicação com a API."));
        exit;
    }

    foreach ($usuarios as $u) {
        if (isset($u["email"]) && $u["email"] === $email && $u["senha"] === $senha) {
            $_SESSION["usuario"] = $u;
            header("Location: feed.php");
            exit;
        }
    }

    header("Location: index.php?erro=" . urlencode("E-mail ou senha incorretos, ou conta ainda não cadastrada."));
    exit;
}

// 3. SAIR
if ($acao === "sair") {
    session_destroy();
    header("Location: index.php?sucesso=" . urlencode("Você saiu da sua conta."));
    exit;
}

// 4. CRIAR NOVA PUBLICAÇÃO (Aceita arquivo enviado do PC OU link da internet)
if ($acao === "publicar" && isset($_SESSION["usuario"])) {
    $legenda = trim($_POST["legenda"] ?? "");
    $imagemFinal = trim($_POST["imagem_url"] ?? "");

    // Se o usuário escolheu um arquivo de imagem do computador
    if (isset($_FILES["arquivo_foto"]) && $_FILES["arquivo_foto"]["error"] === UPLOAD_ERR_OK) {
        $pastaUploads = __DIR__ . "/uploads/";
        if (!is_dir($pastaUploads)) {
            mkdir($pastaUploads, 0777, true);
        }
        $extensao = pathinfo($_FILES["arquivo_foto"]["name"], PATHINFO_EXTENSION);
        $nomeArquivo = "post_" . time() . "_" . rand(100, 999) . "." . $extensao;

        if (move_uploaded_file($_FILES["arquivo_foto"]["tmp_name"], $pastaUploads . $nomeArquivo)) {
            $imagemFinal = "uploads/" . $nomeArquivo;
        }
    }

    if ($imagemFinal === "") {
        $imagemFinal = "https://picsum.photos/seed/" . rand(1, 999) . "/700/450";
    }

    $res = chamarApi($url_api . "?rota=publicacao", "POST", [
        "id_usuario" => (string)$_SESSION["usuario"]["id_usuario"],
        "legenda" => $legenda,
        "imagem_url" => $imagemFinal,
        "data_publicacao" => date("Y-m-d H:i:s")
    ]);

    if (isset($res["id"])) {
        header("Location: feed.php?sucesso=" . urlencode("Publicação compartilhada no feed!"));
    } else {
        $msg = $res["erro"] ?? "Erro ao publicar.";
        header("Location: feed.php?erro=" . urlencode($msg));
    }
    exit;
}

// 5. EXCLUIR PUBLICAÇÃO
if ($acao === "excluir_post" && isset($_SESSION["usuario"])) {
    $idPost = $_POST["id_publicacao"] ?? "";
    if ($idPost !== "") {
        chamarApi($url_api . "?rota=publicacao&id=" . $idPost, "DELETE");
    }
    header("Location: perfil.php?sucesso=" . urlencode("Publicação removida."));
    exit;
}

// 6. CURTIR / DESCURTIR
if ($acao === "curtir" && isset($_SESSION["usuario"])) {
    $idPublicacao = $_POST["id_publicacao"] ?? "";
    $idCurtida = $_POST["id_curtida"] ?? "";
    $volta = $_POST["volta"] ?? "feed.php";

    if ($idCurtida !== "") {
        chamarApi($url_api . "?rota=curtida&id=" . $idCurtida, "DELETE");
    } else {
        chamarApi($url_api . "?rota=curtida", "POST", [
            "id_usuario" => (string)$_SESSION["usuario"]["id_usuario"],
            "id_publicacao" => (string)$idPublicacao,
            "data_curtida" => date("Y-m-d H:i:s")
        ]);
    }

    header("Location: " . $volta);
    exit;
}

header("Location: index.php");