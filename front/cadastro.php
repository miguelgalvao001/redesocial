<?php
session_start();
if (isset($_SESSION["usuario"])) {
    header("Location: feed.php");
    exit;
}
$erro = $_GET["erro"] ?? "";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VibeGram • Criar Conta</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

  <div class="auth-wrapper">
    <div class="auth-showcase">
      <div class="logo-pill">
        <span class="logo-icon">VG</span>
        <span>VibeGram</span>
      </div>
      <h1>Crie sua identidade no VibeGram.</h1>
      <p>Em poucos segundos seu perfil estará ativo no banco de dados para publicar fotos e curtir posts.</p>

      <div class="showcase-cards">
        <div class="mini-card">
          <strong>Passo 1: Cadastre-se</strong>
          <span>Escolha seu @username exclusivo.</span>
        </div>
        <div class="mini-card">
          <strong>Passo 2: Entre no Feed</strong>
          <span>Faça login e comece a compartilhar momentos.</span>
        </div>
      </div>
    </div>

    <div class="auth-panel">
      <div class="auth-header">
        <h2>Criar nova conta</h2>
        <p>Preencha os campos abaixo para se registrar</p>
      </div>

      <?php if ($erro !== ""): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <form method="POST" action="acoes.php" class="auth-form">
        <input type="hidden" name="acao" value="cadastro">

        <div class="field">
          <label>Nome completo</label>
          <input type="text" name="nome" placeholder="Ex: Ana Silva" required>
        </div>

        <div class="field">
          <label>Nome de usuário (@)</label>
          <input type="text" name="username" placeholder="Ex: anasilva" required>
        </div>

        <div class="field">
          <label>E-mail</label>
          <input type="email" name="email" placeholder="ana@email.com" required>
        </div>

        <div class="field">
          <label>Senha</label>
          <input type="password" name="senha" placeholder="Mínimo de 4 caracteres" required>
        </div>

        <button type="submit" class="btn-primary">Concluir Cadastro</button>
      </form>

      <div class="auth-footer">
        Já possui uma conta? <a href="index.php">Fazer login</a>
      </div>
    </div>
  </div>

</body>
</html>