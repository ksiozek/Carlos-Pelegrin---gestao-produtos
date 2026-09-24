<?php
require_once 'inc/auth.php';
exigirLogin();

$titulo = 'Gerenciar';
$scripts = ['gerenciar.js'];
include 'inc/topo.php';
?>

<h3 class="mb-1">Gerenciar cadastros</h3>
<p class="text-muted">Edite direto na tabela e clique em <strong>Salvar</strong>. A página não recarrega (AJAX).</p>

<div id="mensagem" class="alert d-none" role="alert"></div>

<div class="card p-3 mb-4">
    <h5>Fornecedores</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle tabela-ajax">
            <thead><tr><th>Nome</th><th>CNPJ</th><th>Telefone</th><th>E-mail</th><th>Ações</th></tr></thead>
            <tbody id="corpo-fornecedor"><tr><td colspan="5">Carregando...</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card p-3 mb-4">
    <h5>Produtos</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle tabela-ajax">
            <thead><tr><th>Produto</th><th>Descrição</th><th>Preço (R$)</th><th>Fornecedor</th><th>Ações</th></tr></thead>
            <tbody id="corpo-produto"><tr><td colspan="5">Carregando...</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card p-3 mb-4">
    <h5>Minhas cestas</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle tabela-ajax">
            <thead><tr><th>Nome da cesta</th><th>Ações</th></tr></thead>
            <tbody id="corpo-cesta"><tr><td colspan="2">Carregando...</td></tr></tbody>
        </table>
    </div>
</div>

<?php include 'inc/rodape.php'; ?>
