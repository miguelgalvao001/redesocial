<?php
require_once __DIR__ . "/../models/CurtidaModel.php";

function receberDadosCurtida(){
    $dados = json_decode(file_get_contents("php://input"), true);
    if(!is_array($dados)){
        http_response_code(400);
        echo json_encode(["erro" => "Envie um objeto JSON com os dados da curtida!"]);
        return null;
    }
    foreach (["id_usuario", "id_publicacao", "data_curtida"] as $campo){
        if (!isset($dados[$campo]) || !is_string($dados[$campo]) || (trim($dados[$campo]) === "")){
            http_response_code(400);
            echo json_encode(["erro" => "O campo $campo é obrigatório e deve ser um texto não vazio."]);
            return null;
        }
    }
    return $dados;
}

function receberidCurtida(){
    $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]);
    if ($id === false){
        http_response_code(400);
        echo json_encode(["erro" => "Informe um ID de curtida válida"]);
        return null;
    }
    return $id;
}

function createCurtida($con){
    $dados = receberDadosCurtida();
    if ($dados === null) return;
    $id = inserirCurtida($con, $dados);
    http_response_code(201);
    echo json_encode(["mensagem" => "Curtida criada com sucesso!", "id" => $id]);
}

function readCurtida($con){
    $curtidas = listarCurtidas($con);
    echo json_encode($curtidas);
}

function updateCurtida($con){
    $id = receberidCurtida();
    if ($id === null) return;

    $dados = receberDadosCurtida();
    if ($dados === null) return;
    atualizarCurtida($con, $id, $dados);
    echo json_encode(["mensagem" => "Curtida atualizada com sucesso!"]);
}

function deleteCurtida($con){
    $id = receberidCurtida();
    if ($id === null) return;

    removerCurtida($con, $id);
    echo json_encode(["mensagem" => "Curtida removida com sucesso!"]);
}