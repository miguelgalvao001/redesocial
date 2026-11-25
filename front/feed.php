<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php?erro=" . urlencode("Faça login para acessar o feed."));
    exit;
}

$url_api = "http://localhost/redesocial/back/api.php";
$usuarioLogado = $_SESSION["usuario"];
$erro = $_GET["erro"] ?? "";
$sucesso = $_GET["sucesso"] ?? "";
$filtroAutor = $_GET["autor"] ?? "";

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
  <title>VibeGram • Página Inicial</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="app-shell">
    <!-- BARRA LATERAL ESQUERDA -->
    <aside class="sidebar-left">
      <div class="brand">
        <span class="logo-icon">VG</span>
        <span class="brand-name">VibeGram</span>
      </div>

      <nav class="nav-links">
        <a href="feed.php" class="nav-item active">
          <span class="nav-icon">🏠</span>
          <span>Página Inicial</span>
        </a>
        <a href="explorar.php" class="nav-item">
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
            <span>@<?= htmlspecialchars(ltrim($usuarioLogado["username"], "@")) ?></span>
          </div>
        </a>
        <a href="acoes.php?acao=sair" class="btn-logout">Sair da conta</a>
      </div>
    </aside>

    <!-- FEED CENTRAL -->
    <main class="feed-column">
      <!-- BARRA DE STORIES INTERATIVA (CLIQUE FILTRA POSTS DO USUÁRIO) -->
      <section class="stories-bar">
        <a href="feed.php" class="story-item">
          <div class="story-ring" style="<?= $filtroAutor === "" ? "" : "opacity:0.5;" ?>">
            <div class="story-avatar">★</div>
          </div>
          <span>Todos</span>
        </a>
        <?php foreach ($usuarios as $u): ?>
          <?php $userClean = ltrim($u["username"], "@"); ?>
          <a href="feed.php?autor=<?= $u["id_usuario"] ?>" class="story-item">
            <div class="story-ring" style="<?= ($filtroAutor !== "" && $filtroAutor != $u["id_usuario"]) ? "opacity:0.5;" : "" ?>">
              <div class="story-avatar"><?= strtoupper(substr($u["nome"], 0, 1)) ?></div>
            </div>
            <span>@<?= htmlspecialchars($userClean) ?></span>
          </a>
        <?php endforeach; ?>
      </section>

      <?php if ($erro !== ""): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <?php if ($sucesso !== ""): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($sucesso) ?></div>
      <?php endif; ?>

      <?php if ($filtroAutor !== "" && isset($mapaUsuarios[$filtroAutor])): ?>
        <div class="alert alert-success" style="display:flex; justify-content:space-between; align-items:center;">
          <span>Mostrando publicações de <strong>@<?= htmlspecialchars(ltrim($mapaUsuarios[$filtroAutor]["username"], "@")) ?></strong></span>
          <a href="feed.php" style="font-weight:700; text-decoration:underline;">Limpar filtro</a>
        </div>
      <?php endif; ?>

      <!-- COMPOSER COM UPLOAD DO COMPUTADOR OU URL -->
      <section class="composer-card">
        <div class="composer-top">
          <div class="avatar"><?= strtoupper(substr($usuarioLogado["nome"], 0, 1)) ?></div>
          <strong>Criar nova publicação</strong>
        </div>

        <form method="POST" action="acoes.php" enctype="multipart/form-data" class="composer-form">
          <input type="hidden" name="acao" value="publicar">

          <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
            <div>
              <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">📁 Enviar foto do computador:</label>
              <input type="file" name="arquivo_foto" accept="image/*" style="margin-bottom:0; padding:8px;">
            </div>
            <div>
              <label style="font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:4px;">🔗 Ou cole o link da foto:</label>
              <input type="text" name="imagem_url" placeholder="https://picsum.photos/700/450" style="margin-bottom:0;">
            </div>
          </div>

          <textarea name="legenda" rows="2" placeholder="Escreva uma legenda para seus seguidores, <?= htmlspecialchars($usuarioLogado["nome"]) ?>..." required></textarea>
          <div class="composer-actions">
            <span class="hint">Escolha uma imagem do seu PC ou cole uma URL acima</span>
            <button type="submit" class="btn-primary btn-sm">Publicar</button>
          </div>
        </form>
      </section>

      <!-- LISTA DE PUBLICAÇÕES -->
      <?php
        $postsExibir = array_reverse($publicacoes);
        if ($filtroAutor !== "") {
            $postsExibir = array_filter($postsExibir, fn($p) => $p["id_usuario"] == $filtroAutor);
        }
      ?>

      <?php if (empty($postsExibir)): ?>
        <div class="empty-state">
          <h3>Nenhuma publicação encontrada</h3>
          <p>Compartilhe uma foto acima para inaugurar este espaço!</p>
        </div>
      <?php endif; ?>

      <?php foreach ($postsExibir as $post): ?>
        <?php
          $autor = $mapaUsuarios[$post["id_usuario"]] ?? ["nome" => "Usuário", "username" => "usuario"];
          $autorUser = ltrim($autor["username"], "@");

          $totalCurtidas = 0;
          $minhaCurtidaId = "";
          foreach ($curtidas as $c) {
              if ($c["id_publicacao"] == $post["id_publicacao"]) {
                  $totalCurtidas++;
                  if ($c["id_usuario"] == $usuarioLogado["id_usuario"]) {
                      $minhaCurtidaId = $c["id_curtida"];
                  }
              }
          }
          $fallbackImg = "https://picsum.photos/seed/post" . $post["id_publicacao"] . "/700/450";
        ?>

        <article class="post-card">
          <header class="post-header">
            <div class="post-author">
              <div class="avatar"><?= strtoupper(substr($autor["nome"], 0, 1)) ?></div>
              <div>
                <strong><?= htmlspecialchars($autor["nome"]) ?></strong>
                <span>@<?= htmlspecialchars($autorUser) ?> • <?= htmlspecialchars($post["data_publicacao"] ?? "") ?></span>
              </div>
            </div>
            <?php if ($post["id_usuario"] == $usuarioLogado["id_usuario"]): ?>
              <span class="badge-owner">Seu post</span>
            <?php endif; ?>
          </header>

          <div class="post-media">
            <img src="<?= htmlspecialchars($post["imagem_url"]) ?>" onerror="this.onerror=null;this.src='<?= $fallbackImg ?>';" alt="Publicação de <?= htmlspecialchars($autorUser) ?>">
          </div>

          <div class="post-body">
            <div class="post-toolbar">
              <form method="POST" action="acoes.php">
                <input type="hidden" name="acao" value="curtir">
                <input type="hidden" name="id_publicacao" value="<?= $post["id_publicacao"] ?>">
                <input type="hidden" name="id_curtida" value="<?= $minhaCurtidaId ?>">
                <input type="hidden" name="volta" value="feed.php<?= $filtroAutor !== "" ? "?autor=" . urlencode($filtroAutor) : "" ?>">

                <button type="submit" class="like-btn <?= $minhaCurtidaId !== "" ? "liked" : "" ?>">
                  <?= $minhaCurtidaId !== "" ? "❤️ Curtido" : "🤍 Curtir" ?>
                  <span class="like-count"><?= $totalCurtidas ?></span>
                </button>
              </form>
            </div>

            <p class="post-caption">
              <strong>@<?= htmlspecialchars($autorUser) ?></strong>
              <?= htmlspecialchars($post["legenda"]) ?>
            </p>
          </div>
        </article>
      <?php endforeach; ?>
    </main>

    <!-- COLUNA DIREITA -->
    <aside class="sidebar-right">
      <div class="widget-card">
        <div class="widget-header">
          <h4>Membros no VibeGram</h4>
          <a href="explorar.php">Ver todos</a>
        </div>

        <div class="suggestions-list">
          <?php foreach ($usuarios as $u): ?>
            <?php if ($u["id_usuario"] == $usuarioLogado["id_usuario"]) continue; ?>
            <a href="feed.php?autor=<?= $u["id_usuario"] ?>" class="suggestion-row">
              <div class="avatar avatar-sm"><?= strtoupper(substr($u["nome"], 0, 1)) ?></div>
              <div class="suggestion-info">
                <strong><?= htmlspecialchars($u["nome"]) ?></strong>
                <span>@<?= htmlspecialchars(ltrim($u["username"], "@")) ?></span>
              </div>
              <span class="status-dot">Ver posts</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="widget-card">
        <h4>Estatísticas da Rede</h4>
        <div class="network-stats">
          <div>
            <strong><?= count($usuarios) ?></strong>
            <span>Membros</span>
          </div>
          <div>
            <strong><?= count($publicacoes) ?></strong>
            <span>Posts</span>
          </div>
          <div>
            <strong><?= count($curtidas) ?></strong>
            <span>Curtidas</span>
          </div>
        </div>
      </div>
    </aside>
  </div>

</body>
</html>