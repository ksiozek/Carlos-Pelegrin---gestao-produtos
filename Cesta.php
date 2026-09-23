<?php
require_once __DIR__ . '/../config/Conexao.php';

class Cesta
{
    // mínimo de produtos marcados para poder adicionar na cesta (validação do enunciado)
    const MINIMO_PRODUTOS = 2;

    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getConexao();
    }

    public function criar($usuarioId, $nome)
    {
        $stmt = $this->pdo->prepare('INSERT INTO cestas (usuario_id, nome) VALUES (?, ?)');
        $stmt->execute([$usuarioId, $nome]);
        return (int) $this->pdo->lastInsertId();
    }

    public function listarDoUsuario($usuarioId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM cestas WHERE usuario_id = ? ORDER BY id');
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    // busca a cesta SÓ se for do usuário logado (evita mexer na cesta dos outros)
    public function buscar($id, $usuarioId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM cestas WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$id, $usuarioId]);
        return $stmt->fetch();
    }

    // se o usuário não tem nenhuma cesta, cria uma padrão
    public function garantirCestaPadrao($usuarioId)
    {
        if (count($this->listarDoUsuario($usuarioId)) === 0) {
            $this->criar($usuarioId, 'Minha Cesta');
        }
    }

    public function atualizarNome($id, $usuarioId, $nome)
    {
        $stmt = $this->pdo->prepare('UPDATE cestas SET nome = ? WHERE id = ? AND usuario_id = ?');
        return $stmt->execute([$nome, $id, $usuarioId]);
    }

    public function excluir($id, $usuarioId)
    {
        $stmt = $this->pdo->prepare('DELETE FROM cestas WHERE id = ? AND usuario_id = ?');
        return $stmt->execute([$id, $usuarioId]);
    }

    // INSERT IGNORE: se o produto já está na cesta, a chave única impede duplicar
    // O SELECT garante que o produto realmente existe
    public function adicionarProdutos($cestaId, array $idsProdutos)
    {
        $sql = 'INSERT IGNORE INTO cesta_produtos (cesta_id, produto_id)
                SELECT ?, id FROM produtos WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        foreach ($idsProdutos as $idProduto) {
            $stmt->execute([$cestaId, $idProduto]);
        }
    }

    public function removerProduto($cestaId, $produtoId)
    {
        $stmt = $this->pdo->prepare('DELETE FROM cesta_produtos WHERE cesta_id = ? AND produto_id = ?');
        return $stmt->execute([$cestaId, $produtoId]);
    }

    public function produtosDaCesta($cestaId)
    {
        $sql = 'SELECT p.id, p.nome, p.descricao, p.preco, f.nome AS fornecedor_nome
                FROM cesta_produtos cp
                INNER JOIN produtos p ON p.id = cp.produto_id
                INNER JOIN fornecedores f ON f.id = p.fornecedor_id
                WHERE cp.cesta_id = ?
                ORDER BY p.nome';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cestaId]);
        return $stmt->fetchAll();
    }

    // resumo: quantidade de produtos e valor total
    public function resumo($cestaId)
    {
        $sql = 'SELECT COUNT(*) AS quantidade, COALESCE(SUM(p.preco), 0) AS total
                FROM cesta_produtos cp
                INNER JOIN produtos p ON p.id = cp.produto_id
                WHERE cp.cesta_id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cestaId]);
        return $stmt->fetch();
    }
}
