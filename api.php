<?php
// "Backend" do AJAX: recebe pedidos do JavaScript e responde em JSON.
// Parâmetros: entidade (fornecedor | produto | cesta) e acao (listar | atualizar | excluir)

require_once 'inc/auth.php';
require_once 'inc/funcoes.php';
require_once 'classes/Fornecedor.php';
require_once 'classes/Produto.php';
require_once 'classes/Cesta.php';

header('Content-Type: application/json; charset=utf-8');

function responder($ok, $mensagem, $dados = [])
{
    echo json_encode(['ok' => $ok, 'mensagem' => $mensagem, 'dados' => $dados]);
    exit;
}

if (!estaLogado()) {
    http_response_code(401);
    responder(false, 'Sua sessão expirou. Entre novamente.');
}

$usuarioId = $_SESSION['usuario_id'];
$entidade  = $_REQUEST['entidade'] ?? '';
$acao      = $_REQUEST['acao'] ?? '';
$id        = (int) ($_POST['id'] ?? 0);

try {
    // ---------- FORNECEDOR ----------
    if ($entidade === 'fornecedor') {
        $obj = new Fornecedor();

        if ($acao === 'listar') {
            responder(true, '', $obj->listar());
        }
        if ($acao === 'atualizar') {
            $nome = trim($_POST['nome'] ?? '');
            if ($id <= 0 || $nome === '') {
                responder(false, 'O nome do fornecedor é obrigatório.');
            }
            $obj->atualizar($id, $nome, trim($_POST['cnpj'] ?? ''), trim($_POST['telefone'] ?? ''), trim($_POST['email'] ?? ''));
            responder(true, 'Fornecedor atualizado!');
        }
        if ($acao === 'excluir') {
            $obj->excluir($id);
            responder(true, 'Fornecedor excluído!');
        }
    }

    // ---------- PRODUTO ----------
    if ($entidade === 'produto') {
        $obj = new Produto();

        if ($acao === 'listar') {
            responder(true, '', $obj->listar());
        }
        if ($acao === 'atualizar') {
            $nome = trim($_POST['nome'] ?? '');
            $preco = lerPreco($_POST['preco'] ?? '');
            $fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);
            if ($id <= 0 || $nome === '' || $preco === null || $fornecedorId <= 0) {
                responder(false, 'Confira nome, preço e fornecedor do produto.');
            }
            $obj->atualizar($id, $nome, trim($_POST['descricao'] ?? ''), $preco, $fornecedorId);
            responder(true, 'Produto atualizado!');
        }
        if ($acao === 'excluir') {
            $obj->excluir($id);
            responder(true, 'Produto excluído!');
        }
    }

    // ---------- CESTA ----------
    if ($entidade === 'cesta') {
        $obj = new Cesta();

        if ($acao === 'listar') {
            responder(true, '', $obj->listarDoUsuario($usuarioId));
        }
        if ($acao === 'atualizar') {
            $nome = trim($_POST['nome'] ?? '');
            if ($id <= 0 || $nome === '') {
                responder(false, 'Dê um nome para a cesta.');
            }
            $obj->atualizarNome($id, $usuarioId, $nome);
            responder(true, 'Cesta atualizada!');
        }
        if ($acao === 'excluir') {
            $obj->excluir($id, $usuarioId);
            responder(true, 'Cesta excluída!');
        }
    }

    responder(false, 'Pedido inválido.');

} catch (PDOException $ex) {
    // 23000 = violação de chave (ex: fornecedor ainda tem produtos)
    if ($ex->getCode() == '23000') {
        responder(false, 'Não foi possível concluir: este registro está em uso (ex: fornecedor com produtos).');
    }
    responder(false, 'Erro no banco de dados.');
}
