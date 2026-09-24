<?php
require_once 'inc/auth.php';
require_once 'inc/funcoes.php';
require_once 'classes/Fornecedor.php';
require_once 'classes/Produto.php';
require_once 'classes/Cesta.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$abaAtiva = 'fornecedor';

try {
    $fornecedorObj = new Fornecedor();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = $_POST['tipo'] ?? '';
        $abaAtiva = $tipo;

        if ($tipo === 'fornecedor') {
            $nome = trim($_POST['nome'] ?? '');
            if ($nome === '') {
                setMsg('danger', 'O nome do fornecedor é obrigatório.');
            } else {
                $fornecedorObj->cadastrar($nome, trim($_POST['cnpj'] ?? ''), trim($_POST['telefone'] ?? ''), trim($_POST['email'] ?? ''));
                setMsg('success', 'Fornecedor cadastrado com sucesso!');
            }
        } elseif ($tipo === 'produto') {
            $nome = trim($_POST['nome'] ?? '');
            $preco = lerPreco($_POST['preco'] ?? '');
            $fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);

            if ($nome === '' || $preco === null || $fornecedorId <= 0) {
                setMsg('danger', 'Preencha nome, preço (válido) e fornecedor do produto.');
            } else {
                (new Produto())->cadastrar($nome, trim($_POST['descricao'] ?? ''), $preco, $fornecedorId);
                setMsg('success', 'Produto cadastrado com sucesso!');
            }
        } elseif ($tipo === 'cesta') {
            $nome = trim($_POST['nome'] ?? '');
            if ($nome === '') {
                setMsg('danger', 'Dê um nome para a cesta.');
            } else {
                (new Cesta())->criar($usuarioId, $nome);
                setMsg('success', 'Cesta criada com sucesso!');
            }
        }

        // padrão POST -> redirect -> GET (evita reenviar o formulário ao atualizar a página)
        header('Location: cadastros.php?aba=' . urlencode($abaAtiva));
        exit;
    }

    $abaAtiva = $_GET['aba'] ?? 'fornecedor';
    $fornecedores = $fornecedorObj->listar();
} catch (PDOException $ex) {
    setMsg('danger', 'Erro no banco de dados. Confira se o MySQL está ligado.');
    $fornecedores = [];
}

$titulo = 'Cadastros';
include 'inc/topo.php';

function abaClasse($nome, $ativa)
{
    return $nome === $ativa ? 'active' : '';
}
?>

<h3 class="mb-3">Cadastros</h3>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link <?= abaClasse('fornecedor', $abaAtiva) ?>" data-bs-toggle="tab" data-bs-target="#aba-fornecedor" type="button">Fornecedor</button></li>
    <li class="nav-item"><button class="nav-link <?= abaClasse('produto', $abaAtiva) ?>" data-bs-toggle="tab" data-bs-target="#aba-produto" type="button">Produto</button></li>
    <li class="nav-item"><button class="nav-link <?= abaClasse('cesta', $abaAtiva) ?>" data-bs-toggle="tab" data-bs-target="#aba-cesta" type="button">Cesta</button></li>
</ul>

<div class="tab-content">

    <!-- FORNECEDOR -->
    <div class="tab-pane fade <?= $abaAtiva === 'fornecedor' ? 'show active' : '' ?>" id="aba-fornecedor">
        <div class="card p-4">
            <h5 class="mb-3">Novo fornecedor</h5>
            <form method="post">
                <input type="hidden" name="tipo" value="fornecedor">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CNPJ</label>
                        <input type="text" class="form-control" name="cnpj" maxlength="18" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" name="telefone" maxlength="20">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>
                <button class="btn btn-primary mt-3">Salvar fornecedor</button>
            </form>
        </div>
    </div>

    <!-- PRODUTO -->
    <div class="tab-pane fade <?= $abaAtiva === 'produto' ? 'show active' : '' ?>" id="aba-produto">
        <div class="card p-4">
            <h5 class="mb-3">Novo produto</h5>
            <?php if (empty($fornecedores)): ?>
                <div class="alert alert-warning mb-0">Cadastre pelo menos um fornecedor antes de cadastrar produtos.</div>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="tipo" value="produto">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome *</label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preço (R$) *</label>
                            <input type="text" class="form-control" name="preco" placeholder="0,00" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fornecedor *</label>
                            <select class="form-select" name="fornecedor_id" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= e($f['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <input type="text" class="form-control" name="descricao" maxlength="255">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3">Salvar produto</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- CESTA -->
    <div class="tab-pane fade <?= $abaAtiva === 'cesta' ? 'show active' : '' ?>" id="aba-cesta">
        <div class="card p-4">
            <h5 class="mb-3">Nova cesta</h5>
            <form method="post">
                <input type="hidden" name="tipo" value="cesta">
                <label class="form-label">Nome da cesta *</label>
                <input type="text" class="form-control" name="nome" maxlength="80" placeholder="Ex: Compras do mês" required>
                <button class="btn btn-primary mt-3">Criar cesta</button>
            </form>
        </div>
    </div>
</div>

<?php include 'inc/rodape.php'; ?>
