<?php
require_once __DIR__ . "/../models/PublicacaoModel.php";

function receberDadosPublicacao(){
    $dados = json_decode(file_get_contents("php://input"), true);
    if(!is_array($dados)){
        http_response_code(400);
        echo json_encode(["erro" => "Envie um objeto JSON com os dados da publicacao!"]);
        return null;
    }
    foreach (["id_usuario", "legenda", "imagem_url", "data_publicacao"] as $campo){
        if (!isset($dados[$campo]) || !is_string($dados[$campo]) || (trim($dados[$campo]) === "")){
            http_response_code(400);
            echo json_encode(["erro" => "O campo $campo é obrigatório e deve ser um texto não vazio."]);
            return null;
        }
    }
    return $dados;
}

function receberidPublicacao(){
    $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]);
    if ($id === false){
        http_response_code(400);
        echo json_encode(["erro" => "Informe um ID de publicação válida"]);
        return null;
    }
    return $id;
}

function createPublicacao($con){
    $dados = receberDadosPublicacao();
    if ($dados === null) return;
    $id = inserirPublicacao($con, $dados);
    http_response_code(201);
    echo json_encode(["mensagem" => "Publicação criada com sucesso!", "id" => $id]);
}

function readPublicacao($con){
    $publicacoes = listarPublicacoes($con);
    echo json_encode($publicacoes);
}

function updatePublicacao($con){
    $id = receberidPublicacao();
    if ($id === null) return;

    $dados = receberDadosPublicacao();
    if ($dados === null) return;
    atualizarPublicacao($con, $id, $dados);
    echo json_encode(["mensagem" => "Publicação atualizada com sucesso!"]);
}

function deletePublicacao($con){
    $id = receberidPublicacao();
    if ($id === null) return;

    removerPublicacao($con, $id);
    echo json_encode(["mensagem" => "Publicação removida com sucesso!"]);
}