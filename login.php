<?php
require_once 'inc/auth.php';
require_once 'classes/Usuario.php';

if (estaLogado()) {
    header('Location: cadastros.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    try {
        $usuario = (new Usuario())->autenticar($email, $senha);

        if ($usuario) {
            session_regenerate_id(true); // evita fixação de sessão
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            header('Location: cadastros.php');
            exit;
        }
        $erro = 'E-mail ou senha incorretos.';
    } catch (PDOException $ex) {
        $erro = 'Erro ao acessar o banco de dados. Confira se o MySQL está ligado.';
    }
}

$titulo = 'Entrar';
include 'inc/topo.php';
?>

<div class="form-login">
    <div class="card p-4">
        <h4 class="mb-3">Entrar</h4>

        <?php if ($erro): ?>
            <div class="alert alert-danger py-2"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="senha">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button class="btn btn-primary w-100">Entrar</button>
        </form>
        <p class="text-center small mt-3 mb-0">Ainda não tem conta? <a href="registrar.php">Criar conta</a></p>
    </div>
</div>

<?php include 'inc/rodape.php'; ?>
