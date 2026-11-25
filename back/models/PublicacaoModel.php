<?php
function inserirPublicacao($con, $dados){
    $stmt = $con->prepare("INSERT INTO PUBLICACOES (id_usuario, legenda, imagem_url, data_publicacao) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $dados['id_usuario'],
        $dados['legenda'],
        $dados['imagem_url'],
        $dados['data_publicacao']
    ]);
    return $con->lastInsertId();
}

function listarPublicacoes($con){
    $stmt = $con->query("SELECT * FROM publicacoes");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function atualizarPublicacao($con, $id, $dados){
    $stmt = $con->prepare("UPDATE publicacoes SET legenda = ?, imagem_url = ?, data_publicacao = ? WHERE id_publicacao = ?");
    $stmt->execute([
        $dados["legenda"],
        $dados["imagem_url"],
        $dados["data_publicacao"],
        $id
    ]);
}

function removerPublicacao($con, $id){
    $stmt = $con->prepare("DELETE FROM publicacoes WHERE id_publicacao = ?");
    $stmt->execute([$id]);
}