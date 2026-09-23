<?php
require_once __DIR__ . '/../config/Conexao.php';

class Usuario
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getConexao();
    }

    // O enunciado pede SHA (o "SHA254" do texto deve ser o SHA-256, que gera 64 caracteres)
    public static function gerarHash($senha)
    {
        return hash('sha256', $senha);
    }

    public function emailExiste($email)
    {
        $stmt = $this->pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    // retorna o id do novo usuário
    public function cadastrar($nome, $email, $senha)
    {
        $stmt = $this->pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
        $stmt->execute([$nome, $email, self::gerarHash($senha)]);
        return (int) $this->pdo->lastInsertId();
    }

    // retorna os dados do usuário se e-mail e senha baterem, senão false
    public function autenticar($email, $senha)
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && hash_equals($usuario['senha_hash'], self::gerarHash($senha))) {
            return $usuario;
        }
        return false;
    }
}
