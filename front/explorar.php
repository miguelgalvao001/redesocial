<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$url_api = "http://localhost/redesocial/back/api.php";
$usuarioLogado = $_SESSION["usuario"];
$meuUsernameLimpo = ltrim($usuarioLogado["username"], "@");

$usuarios = json_decode(@file_get_contents($url_api . "?rota=usuario"), true) ?? [];
$publicacoes = json_decode(@file_get_contents($url_api . "?rota=publicacao"), true) ?? [];
$curtidas = json_decode(@file_get_contents($url_api . "?rota=curtida"), true) ?? [];

$mapaUsuarios = [];
foreach ($usuarios as $u) {
    $mapaUsuarios[$u["id_usuario"]] = $u;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VibeGram • Explorar</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="app-shell">
    <aside class="sidebar-left">
      <div class="brand">
        <span class="logo-icon">VG</span>
        <span class="brand-name">VibeGram</span>
      </div>

      <nav class="nav-links">
        <a href="feed.php" class="nav-item">
          <span class="nav-icon">🏠</span>
          <span>Página Inicial</span>
        </a>
        <a href="explorar.php" class="nav-item active">
          <span class="nav-icon">🧭</span>
          <span>Explorar</span>
        </a>
        <a href="perfil.php" class="nav-item">
          <span class="nav-icon">👤</span>
          <span>Meu Perfil</span>
        </a>
      </nav>

      <div class="sidebar-bottom">
        <a href="perfil.php" class="user-chip">
          <div class="avatar"><?= strtoupper(substr($usuarioLogado["nome"], 0, 1)) ?></div>
          <div class="user-chip-info">
            <strong><?= htmlspecialchars($usuarioLogado["nome"]) ?></strong>
            <span>@<?= htmlspecialchars($meuUsernameLimpo) ?></span>
          </div>
        </a>
        <a href="acoes.php?acao=sair" class="btn-logout">Sair da conta</a>
      </div>
    </aside>

    <main class="wide-column">
      <div class="page-title-bar">
        <h2>Explorar VibeGram</h2>
        <p>Descubra todas as publicações e criadores da comunidade</p>
      </div>

      <div class="explore-grid">
        <?php foreach (array_reverse($publicacoes) as $post): ?>
          <?php
            $autor = $mapaUsuarios[$post["id_usuario"]] ?? ["username" => "usuario"];
            $autorUser = ltrim($autor["username"], "@");
            $qtdCurtidas = 0;
            $minhaCurtidaId = "";
            foreach ($curtidas as $c) {
                if ($c["id_publicacao"] == $post["id_publicacao"]) {
                    $qtdCurtidas++;
                    if ($c["id_usuario"] == $usuarioLogado["id_usuario"]) {
                        $minhaCurtidaId = $c["id_curtida"];
                    }
                }
            }
            $fallbackImg = "https://picsum.photos/seed/post" . $post["id_publicacao"] . "/600/600";
          ?>
          <div class="explore-card">
            <img src="<?= htmlspecialchars($post["imagem_url"]) ?>" onerror="this.onerror=null;this.src='<?= $fallbackImg ?>';" alt="Foto de <?= htmlspecialchars($autorUser) ?>">
            <div class="explore-overlay">
              <div class="explore-meta">
                <strong>@<?= htmlspecialchars($autorUser) ?></strong>
                <p><?= htmlspecialchars($post["legenda"]) ?></p>
              </div>
              <form method="POST" action="acoes.php">
                <input type="hidden" name="acao" value="curtir">
                <input type="hidden" name="id_publicacao" value="<?= $post["id_publicacao"] ?>">
                <input type="hidden" name="id_curtida" value="<?= $minhaCurtidaId ?>">
                <input type="hidden" name="volta" value="explorar.php">
                <button type="submit" class="like-btn <?= $minhaCurtidaId !== "" ? "liked" : "" ?>">
                  <?= $minhaCurtidaId !== "" ? "❤️" : "🤍" ?> <?= $qtdCurtidas ?>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

</body>
</html>