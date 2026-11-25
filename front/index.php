<?php
session_start();
if (isset($_SESSION["usuario"])) {
    header("Location: feed.php");
    exit;
}
$erro = $_GET["erro"] ?? "";
$sucesso = $_GET["sucesso"] ?? "";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VibeGram • Entrar</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

  <div class="auth-wrapper">
    <div class="auth-showcase">
      <div class="logo-pill">
        <span class="logo-icon">VG</span>
        <span>VibeGram</span>
      </div>
      <h1>Conecte-se com o que importa para você.</h1>
      <p>Publique fotos, acompanhe o feed da comunidade em tempo real e interaja com seus amigos em uma experiência limpa e rápida.</p>

      <div class="showcase-cards">
        <div class="mini-card">
          <strong>Feed em Tempo Real</strong>
          <span>Acompanhe as últimas postagens conectadas ao banco.</span>
        </div>
        <div class="mini-card">
          <strong>Interação Instantânea</strong>
          <span>Curta publicações e gerencie seu próprio perfil.</span>
        </div>
      </div>
    </div>

    <div class="auth-panel">
      <div class="auth-header">
        <h2>Bem-vindo de volta</h2>
        <p>Insira seus dados para acessar sua conta</p>
      </div>

      <?php if ($erro !== ""): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <?php if ($sucesso !== ""): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($sucesso) ?></div>
      <?php endif; ?>

      <form method="POST" action="acoes.php" class="auth-form">
        <input type="hidden" name="acao" value="login">

        <div class="field">
          <label>E-mail</label>
          <input type="email" name="email" placeholder="ana@email.com" required>
        </div>

        <div class="field">
          <label>Senha</label>
          <input type="password" name="senha" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Entrar no VibeGram</button>
      </form>

      <div class="auth-footer">
        Ainda não tem uma conta? <a href="cadastro.php">Criar conta gratuita</a>
      </div>
    </div>
  </div>

</body>
</html>