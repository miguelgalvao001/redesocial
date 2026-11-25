<?php
require_once __DIR__ . "/../models/usuariomodel.php";

function receberDadosUsuario() {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dados)) {
        http_response_code(400);
        echo json_encode(["erro" => "Envie um objeto JSON com os dados do usuário!"]);
        return null;
    }
    return $dados;
}

function receberidUsuario() {
    $id =$_GET["id"] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["erro" => "ID do usuário não enviado!"]);
        return null;
    }
    return $id;
}

function createUsuario($con) {$dados = receberDadosUsuario();
    if ($dados === null) return;

    $id = inserirUsuario($con,$dados);
    http_response_code(201);
    echo json_encode(["mensagem" => "Usuário criado com sucesso!", "id" => $id]);
}

function readUsuario($con) {
    $usuarios = listarUsuarios($con);
    echo json_encode($usuarios);
}

function updateUsuario($con) {$id = receberidUsuario();
    if ($id === null) return;

    $dados = receberDadosUsuario();
    if ($dados === null) return;

    atualizarUsuario($con, $id,$dados);
    echo json_encode(["mensagem" => "Senha alterada com sucesso!"]);
}

function deleteUsuario($con) {$id = receberidUsuario();
    if ($id === null) return;

    removerUsuario($con,$id);
    echo json_encode(["mensagem" => "Usuário removido com sucesso!"]);
}