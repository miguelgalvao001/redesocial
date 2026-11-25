<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$url_api = "http://localhost/redesocial/back/api.php";
$usuarioLogado = $_SESSION["usuario"];
$meuUsernameLimpo = ltrim($usuarioLogado["username"], "@");
$sucesso = $_GET["sucesso"] ?? "";

$publicacoes = json_decode(@file_get_contents($url_api . "?rota=publicacao"), true) ?? [];
$curtidas = json_decode(@file_get_contents($url_api . "?rota=curtida"), true) ?? [];

$meusPosts = [];
$curtidasRecebidas = 0;
$curtidasFeitas = 0;

foreach ($curtidas as $c) {
    if ($c["id_usuario"] == $usuarioLogado["id_usuario"]) {
        $curtidasFeitas++;
    }
}

foreach ($publicacoes as $p) {
    if ($p["id_usuario"] == $usuarioLogado["id_usuario"]) {
        $meusPosts[] = $p;
        foreach ($curtidas as $c) {
            if ($c["id_publicacao"] == $p["id_publicacao"]) {
                $curtidasRecebidas++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VibeGram • @<?= htmlspecialchars($meuUsernameLimpo) ?></title>
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
        <a href="explorar.php" class="nav-item">
          <span class="nav-icon">🧭</span>
          <span>Explorar</span>
        </a>
        <a href="perfil.php" class="nav-item active">
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
      <?php if ($sucesso !== ""): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($sucesso) ?></div>
      <?php endif; ?>

      <section class="profile-banner-card">
        <div class="profile-cover"></div>
        <div class="profile-main-info">
          <div class="avatar-xl"><?= strtoupper(substr($usuarioLogado["nome"], 0, 1)) ?></div>
          <div class="profile-text">
            <h2><?= htmlspecialchars($usuarioLogado["nome"]) ?></h2>
            <span class="handle">@<?= htmlspecialchars($meuUsernameLimpo) ?></span>
            <p class="bio-email"><?= htmlspecialchars($usuarioLogado["email"]) ?> • Membro desde <?= htmlspecialchars($usuarioLogado["data_cadastro"] ?? "2026") ?></p>
          </div>
          <a href="feed.php" class="btn-primary btn-sm">+ Nova Foto</a>
        </div>

        <div class="profile-metrics">
          <div class="metric">
            <strong><?= count($meusPosts) ?></strong>
            <span>Publicações</span>
          </div>
          <div class="metric">
            <strong><?= $curtidasRecebidas ?></strong>
            <span>Curtidas recebidas</span>
          </div>
          <div class="metric">
            <strong><?= $curtidasFeitas ?></strong>
            <span>Posts curtidos</span>
          </div>
        </div>
      </section>

      <div class="page-title-bar">
        <h3>Minhas Publicações</h3>
      </div>

      <?php if (empty($meusPosts)): ?>
        <div class="empty-state">
          <h3>Seu perfil ainda está vazio</h3>
          <p>Compartilhe sua primeira foto no feed para vê-la aqui.</p>
          <a href="feed.php" class="btn-primary btn-sm" style="display:inline-block; margin-top:12px;">Ir para o Feed</a>
        </div>
      <?php else: ?>
        <div class="explore-grid">
          <?php foreach (array_reverse($meusPosts) as $post): ?>
            <?php $fallbackImg = "https://picsum.photos/seed/post" . $post["id_publicacao"] . "/600/600"; ?>
            <div class="explore-card">
              <img src="<?= htmlspecialchars($post["imagem_url"]) ?>" onerror="this.onerror=null;this.src='<?= $fallbackImg ?>';" alt="Minha publicação">
              <div class="explore-overlay">
                <div class="explore-meta">
                  <p><?= htmlspecialchars($post["legenda"]) ?></p>
                  <span style="font-size:11px; color:#64748b;"><?= htmlspecialchars($post["data_publicacao"] ?? "") ?></span>
                </div>
                <form method="POST" action="acoes.php">
                  <input type="hidden" name="acao" value="excluir_post">
                  <input type="hidden" name="id_publicacao" value="<?= $post["id_publicacao"] ?>">
                  <button type="submit" class="btn-danger-sm">Excluir</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

</body>
</html>