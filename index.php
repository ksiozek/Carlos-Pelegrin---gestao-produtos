<?php
require_once 'inc/auth.php';

// a página inicial só decide pra onde mandar o usuário
header('Location: ' . (estaLogado() ? 'cadastros.php' : 'login.php'));
exit;
