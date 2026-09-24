<?php
// Classe que cuida da conexão com o MySQL (via PDO).
// Se o banco e as tabelas não existirem, ela mesma cria (exigência do trabalho).

class Conexao
{
    // Se o seu MySQL tiver outra senha, é só mudar aqui
    private static $host    = 'localhost';
    private static $usuario = 'root';
    private static $senha   = '';
    private static $banco   = 'gestao_produtos';

    private static $pdo = null;

    public static function getConexao()
    {
        // se já conectou nessa requisição, reaproveita
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // 1) conecta sem escolher o banco, só pra poder criar ele
        $pdo = new PDO(
            'mysql:host=' . self::$host . ';charset=utf8mb4',
            self::$usuario,
            self::$senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // 2) cria o banco (se não existir) e passa a usar ele
        $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . self::$banco . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE ' . self::$banco);

        // 3) cria as tabelas
        self::criarTabelas($pdo);

        self::$pdo = $pdo;
        return $pdo;
    }

    private static function criarTabelas($pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(120) NOT NULL UNIQUE,
            senha_hash CHAR(64) NOT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS fornecedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            cnpj VARCHAR(18),
            telefone VARCHAR(20),
            email VARCHAR(120)
        ) ENGINE=InnoDB");

        // um produto pertence a UM fornecedor (1:N)
        $pdo->exec("CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            descricao VARCHAR(255),
            preco DECIMAL(10,2) NOT NULL,
            fornecedor_id INT NOT NULL,
            FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB");

        // uma cesta pertence a UM usuário (1:N)
        $pdo->exec("CREATE TABLE IF NOT EXISTS cestas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            nome VARCHAR(80) NOT NULL,
            criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // tabela de ligação: cesta x produto (N:N). Sem coluna de quantidade, 1 unidade de cada.
        $pdo->exec("CREATE TABLE IF NOT EXISTS cesta_produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cesta_id INT NOT NULL,
            produto_id INT NOT NULL,
            UNIQUE KEY unico_produto_na_cesta (cesta_id, produto_id),
            FOREIGN KEY (cesta_id) REFERENCES cestas(id) ON DELETE CASCADE,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");
    }
}
