<?php
require_once __DIR__ . '/../config/Conexao.php';

class Produto
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getConexao();
    }

    public function cadastrar($nome, $descricao, $preco, $fornecedorId)
    {
        $sql = 'INSERT INTO produtos (nome, descricao, preco, fornecedor_id) VALUES (?, ?, ?, ?)';
        return $this->pdo->prepare($sql)->execute([$nome, $descricao, $preco, $fornecedorId]);
    }

    // JOIN com fornecedores para já trazer o nome de quem fornece
    public function listar()
    {
        $sql = 'SELECT p.*, f.nome AS fornecedor_nome
                FROM produtos p
                INNER JOIN fornecedores f ON f.id = p.fornecedor_id
                ORDER BY p.nome';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function atualizar($id, $nome, $descricao, $preco, $fornecedorId)
    {
        $sql = 'UPDATE produtos SET nome = ?, descricao = ?, preco = ?, fornecedor_id = ? WHERE id = ?';
        return $this->pdo->prepare($sql)->execute([$nome, $descricao, $preco, $fornecedorId, $id]);
    }

    public function excluir($id)
    {
        return $this->pdo->prepare('DELETE FROM produtos WHERE id = ?')->execute([$id]);
    }
}
