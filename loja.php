<?php
require_once 'inc/auth.php';
require_once 'inc/funcoes.php';
require_once 'classes/Produto.php';
require_once 'classes/Cesta.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$produtos = [];
$cestas = [];

try {
    $cestaObj = new Cesta();
    $cestaObj->garantirCestaPadrao($usuarioId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cestaId = (int) ($_POST['cesta_id'] ?? 0);
        $ids = array_map('intval', (array) ($_POST['produtos'] ?? []));
        $ids = array_unique(array_filter($ids));

        // validação no servidor (a do JavaScript pode ser burlada)
        if (!$cestaObj->buscar($cestaId, $usuarioId)) {
            setMsg('danger', 'Cesta inválida.');
        } elseif (count($ids) < Cesta::MINIMO_PRODUTOS) {
            setMsg('warning', 'Selecione pelo menos ' . Cesta::MINIMO_PRODUTOS . ' produtos.');
        } else {
            $cestaObj->adicionarProdutos($cestaId, $ids);
            setMsg('success', count($ids) . ' produto(s) enviado(s) para a cesta!');
            header('Location: cesta.php?id=' . $cestaId);
            exit;
        }
        header('Location: loja.php');
        exit;
    }

    $produtos = (new Produto())->listar();
    $cestas = $cestaObj->listarDoUsuario($usuarioId);
} catch (PDOException $ex) {
    setMsg('danger', 'Erro no banco de dados. Confira se o MySQL está ligado.');
}

$titulo = 'Produtos';
$scripts = ['loja.js'];
include 'inc/topo.php';
?>

<h3 class="mb-3">Produtos</h3>

<?php if (empty($produtos)): ?>
    <div class="alert alert-info">
        Ainda não há produtos. <a href="cadastros.php?aba=produto">Cadastre o primeiro</a>.
    </div>
<?php else: ?>
    <form method="post" id="form-loja" data-minimo="<?= Cesta::MINIMO_PRODUTOS ?>">
        <div class="card p-3 mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="form-label mb-0" for="cesta_id">Adicionar na cesta:</label>
                    <select class="form-select" id="cesta_id" name="cesta_id">
                        <?php foreach ($cestas as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <span class="d-block text-muted small">Selecionados</span>
                    <span class="badge bg-secondary fs-6" id="contador">0</span>
                    <span class="small text-muted">(mínimo <?= Cesta::MINIMO_PRODUTOS ?>)</span>
                </div>
                <div class="col-md-3 text-md-end">
                    <button type="submit" class="btn btn-primary mt-3 mt-md-0" id="btn-adicionar">Adicionar à cesta</button>
                </div>
            </div>
            <div class="alert alert-warning py-2 mt-3 mb-0 d-none" id="aviso-validacao"></div>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th style="width:50px"></th><th>Produto</th><th>Descrição</th><th>Fornecedor</th><th class="text-end">Preço</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p): ?>
                            <tr>
                                <td><input type="checkbox" class="form-check-input check-produto" name="produtos[]" value="<?= $p['id'] ?>" id="prod<?= $p['id'] ?>"></td>
                                <td><label for="prod<?= $p['id'] ?>"><?= e($p['nome']) ?></label></td>
                                <td class="text-muted"><?= e($p['descricao']) ?></td>
                                <td><?= e($p['fornecedor_nome']) ?></td>
                                <td class="text-end"><?= moeda($p['preco']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'inc/rodape.php'; ?>
