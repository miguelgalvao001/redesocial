<?php
function inserirCurtida($con, $dados){
    $stmt = $con->prepare("INSERT INTO CURTIDAS (id_usuario, id_publicacao, data_curtida) VALUES (?, ?, ?)");
    $stmt->execute([
        $dados['id_usuario'],
        $dados['id_publicacao'],
        $dados['data_curtida']
    ]);
    return $con->lastInsertId();
}

function listarCurtidas($con){
    $stmt = $con->query("SELECT * FROM curtidas");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function atualizarCurtida($con, $id, $dados){
    $stmt = $con->prepare("UPDATE curtidas SET id_usuario = ?, id_publicacao = ?, data_curtida = ? WHERE id_curtida = ?");
    $stmt->execute([
        $dados["id_usuario"],
        $dados["id_publicacao"],
        $dados["data_curtida"],
        $id
    ]);
}

function removerCurtida($con, $id){
    $stmt = $con->prepare("DELETE FROM curtidas WHERE id_curtida = ?");
    $stmt->execute([$id]);
}