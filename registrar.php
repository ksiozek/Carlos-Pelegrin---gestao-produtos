<?php
require_once 'inc/auth.php';
require_once 'classes/Usuario.php';

if (estaLogado()) {
    header('Location: cadastros.php');
    exit;
}

$erros = [];
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma'] ?? '';

    // validações no servidor (o navegador também valida, mas não dá pra confiar só nele)
    if ($nome === '') {
        $erros[] = 'Informe seu nome.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 6) {
        $erros[] = 'A senha precisa ter pelo menos 6 caracteres.';
    }
    if ($senha !== $confirma) {
        $erros[] = 'A confirmação de senha não confere.';
    }

    if (empty($erros)) {
        try {
            $usuario = new Usuario();
            if ($usuario->emailExiste($email)) {
                $erros[] = 'Já existe uma conta com esse e-mail.';
            } else {
                $usuario->cadastrar($nome, $email, $senha);
                setMsg('success', 'Conta criada! Agora é só entrar.');
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $ex) {
            $erros[] = 'Erro ao acessar o banco de dados. Confira se o MySQL está ligado.';
        }
    }
}

$titulo = 'Criar conta';
include 'inc/topo.php';
?>

<div class="form-login">
    <div class="card p-4">
        <h4 class="mb-3">Criar conta</h4>

        <?php foreach ($erros as $erro): ?>
            <div class="alert alert-danger py-2"><?= e($erro) ?></div>
        <?php endforeach; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label" for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= e($nome) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="senha">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" minlength="6" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="confirma">Confirmar senha</label>
                <input type="password" class="form-control" id="confirma" name="confirma" minlength="6" required>
            </div>
            <button class="btn btn-primary w-100">Cadastrar</button>
        </form>
        <p class="text-center small mt-3 mb-0">Já tem conta? <a href="login.php">Entrar</a></p>
    </div>
</div>

<?php include 'inc/rodape.php'; ?>
