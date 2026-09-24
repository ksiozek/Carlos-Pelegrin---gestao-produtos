<?php
// CONTROLE DE SESSÃO E MENSAGENS RÁPIDAS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaLogado()
{
    return isset($_SESSION['usuario_id']);
}

// coloque no começo de toda página que precisa de login
function exigirLogin()
{
    if (!estaLogado()) {
        header('Location: login.php');
        exit;
    }
}

// guarda uma mensagem para aparecer na próxima tela
function setMsg($tipo, $texto)
{
    $_SESSION['msg'] = ['tipo' => $tipo, 'texto' => $texto];
}

// mostra a mensagem (se tiver) e já apaga
function mostrarMsg()
{
    if (isset($_SESSION['msg'])) {
        $m = $_SESSION['msg'];
        unset($_SESSION['msg']);
        echo '<div class="alert alert-' . $m['tipo'] . ' alert-dismissible fade show" role="alert">'
            . htmlspecialchars($m['texto'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
