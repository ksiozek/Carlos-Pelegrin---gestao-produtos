<?php
require_once 'inc/auth.php';
require_once 'inc/funcoes.php';
require_once 'classes/Cesta.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$cestas = [];
$cesta = null;
$produtos = [];
$resumo = ['quantidade' => 0, 'total' => 0];

try {
    $cestaObj = new Cesta();
    $cestaObj->garantirCestaPadrao($usuarioId);

    // remover um produto da cesta
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cestaId = (int) ($_POST['cesta_id'] ?? 0);
        $produtoId = (int) ($_POST['produto_id'] ?? 0);

        if ($cestaObj->buscar($cestaId, $usuarioId)) {
            $cestaObj->removerProduto($cestaId, $produtoId);
            setMsg('success', 'Produto removido da cesta.');
        }
        header('Location: cesta.php?id=' . $cestaId);
        exit;
    }

    $cestas = $cestaObj->listarDoUsuario($usuarioId);

    // escolhe a cesta pedida na URL; se não existir (ou não for do usuário), usa a primeira
    $cesta = $cestaObj->buscar((int) ($_GET['id'] ?? 0), $usuarioId);
    if (!$cesta) {
        $cesta = $cestas[0];
    }

    $produtos = $cestaObj->produtosDaCesta($cesta['id']);
    $resumo = $cestaObj->resumo($cesta['id']);
} catch (PDOException $ex) {
    setMsg('danger', 'Erro no banco de dados. Confira se o MySQL está ligado.');
}

$titulo = 'Minha Cesta';
include 'inc/topo.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Minha Cesta</h3>

    <?php if (count($cestas) > 1): ?>
        <form method="get" class="d-flex gap-2">
            <select name="id" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($cestas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $cesta && $c['id'] == $cesta['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>
</div>

<?php if ($cesta): ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-3">
            <h5 class="mb-3"><?= e($cesta['nome']) ?></h5>

            <?php if (empty($produtos)): ?>
                <p class="text-muted mb-0">Essa cesta está vazia. <a href="loja.php">Escolha alguns produtos</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Produto</th><th>Fornecedor</th><th class="text-end">Preço</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($produtos as $p): ?>
                                <tr>
                                    <td>
                                        <?= e($p['nome']) ?>
                                        <div class="small text-muted"><?= e($p['descricao']) ?></div>
                                    </td>
                                    <td><?= e($p['fornecedor_nome']) ?></td>
                                    <td class="text-end"><?= moeda($p['preco']) ?></td>
                                    <td class="text-end">
                                        <form method="post" onsubmit="return confirm('Remover este produto da cesta?')">
                                            <input type="hidden" name="cesta_id" value="<?= $cesta['id'] ?>">
                                            <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 resumo-cesta">
            <h5>Resumo</h5>
            <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span>Produtos selecionados</span>
                    <strong><?= (int) $resumo['quantidade'] ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span>Valor total</span>
                    <strong><?= moeda($resumo['total']) ?></strong>
                </li>
            </ul>
            <a href="loja.php" class="btn btn-outline-primary">Adicionar mais produtos</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'inc/rodape.php'; ?>
