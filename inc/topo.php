<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/funcoes.php';
$paginaAtual = basename($_SERVER['PHP_SELF']);

// função pequena só pra marcar o menu da página atual
function ativo($arquivo)
{
    global $paginaAtual;
    return $paginaAtual === $arquivo ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Gestão de Produtos') ?> | Gestão de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/estilo.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
<div class="container">
<a class="navbar-brand" href="index.php">Gestão de Produtos</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="menu">
<?php if (estaLogado()): ?>
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link <?= ativo('cadastros.php') ?>" href="cadastros.php">Cadastros</a></li>
<li class="nav-item"><a class="nav-link <?= ativo('gerenciar.php') ?>" href="gerenciar.php">Gerenciar (AJAX)</a></li>
<li class="nav-item"><a class="nav-link <?= ativo('loja.php') ?>" href="loja.php">Produtos</a></li>
<li class="nav-item"><a class="nav-link <?= ativo('cesta.php') ?>" href="cesta.php">Minha Cesta</a></li>
</ul>
<span class="navbar-text me-3">Olá, <?= e($_SESSION['usuario_nome']) ?></span>
<a class="btn btn-outline-light btn-sm" href="logout.php">Sair</a>
            <?php else: ?>
<ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link <?= ativo('login.php') ?>" href="login.php">Entrar</a></li>
<li class="nav-item"><a class="nav-link <?= ativo('registrar.php') ?>" href="registrar.php">Criar conta</a></li>
</ul>
            <?php endif; ?>
        </div>
</div>
</nav>

<main class="container pb-5">
<?php mostrarMsg(); ?>
