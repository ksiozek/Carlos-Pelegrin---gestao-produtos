<?php
require_once __DIR__ . '/../config/Conexao.php';

class Fornecedor
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getConexao();
    }

    public function cadastrar($nome, $cnpj, $telefone, $email)
    {
        $sql = 'INSERT INTO fornecedores (nome, cnpj, telefone, email) VALUES (?, ?, ?, ?)';
        return $this->pdo->prepare($sql)->execute([$nome, $cnpj, $telefone, $email]);
    }

    public function listar()
    {
        return $this->pdo->query('SELECT * FROM fornecedores ORDER BY nome')->fetchAll();
    }

    public function atualizar($id, $nome, $cnpj, $telefone, $email)
    {
        $sql = 'UPDATE fornecedores SET nome = ?, cnpj = ?, telefone = ?, email = ? WHERE id = ?';
        return $this->pdo->prepare($sql)->execute([$nome, $cnpj, $telefone, $email, $id]);
    }

    // vai dar erro (PDOException) se o fornecedor ainda tiver produtos
    public function excluir($id)
    {
        return $this->pdo->prepare('DELETE FROM fornecedores WHERE id = ?')->execute([$id]);
    }
}
