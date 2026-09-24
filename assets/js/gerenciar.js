// Área AJAX: lista, atualiza e exclui fornecedores, produtos e cestas sem recarregar a página.

// CAMPOS DAS TABELAS (a ordem é a das colunas)
const config = {
    fornecedor: [
        { campo: 'nome', tipo: 'text' },
        { campo: 'cnpj', tipo: 'text' },
        { campo: 'telefone', tipo: 'text' },
        { campo: 'email', tipo: 'email' }
    ],
    produto: [
        { campo: 'nome', tipo: 'text' },
        { campo: 'descricao', tipo: 'text' },
        { campo: 'preco', tipo: 'number' },
        { campo: 'fornecedor_id', tipo: 'select' }
    ],
    cesta: [
        { campo: 'nome', tipo: 'text' }
    ]
};

let fornecedores = [];   
// PRA MONTAR OS SELECTS DE PRODUTOS (NOME DO FORNECEDOR)
let timerMensagem = null;

// escapa texto pra não quebrar o HTML (nem permitir injeção de script)
function esc(texto) {
    const div = document.createElement('div');
    div.textContent = texto == null ? '' : texto;
    return div.innerHTML.replace(/"/g, '&quot;');
}

function mostrarMensagem(texto, ok) {
    const div = document.getElementById('mensagem');
    div.className = 'alert alert-' + (ok ? 'success' : 'danger');
    div.textContent = texto;
    clearTimeout(timerMensagem);
    timerMensagem = setTimeout(() => div.classList.add('d-none'), 4000);
}

// monta uma célula (<td>) com input ou select preenchido
function montarCelula(def, linha) {
    const valor = linha[def.campo];

    if (def.tipo === 'select') {
        const opcoes = fornecedores.map(f =>
            `<option value="${f.id}" ${f.id == valor ? 'selected' : ''}>${esc(f.nome)}</option>`
        ).join('');
        return `<td><select class="form-select form-select-sm" data-campo="${def.campo}">${opcoes}</select></td>`;
    }

    const extra = def.tipo === 'number' ? 'step="0.01" min="0"' : '';
    return `<td><input type="${def.tipo}" class="form-control form-control-sm"
            data-campo="${def.campo}" value="${esc(valor)}" ${extra}></td>`;
}

// busca os dados no servidor e desenha a tabela
async function carregar(entidade) {
    const corpo = document.getElementById('corpo-' + entidade);
    const colunas = config[entidade].length + 1;

    try {
        const resp = await fetch(`api.php?entidade=${entidade}&acao=listar`);
        const json = await resp.json();

        if (!json.ok) {
            mostrarMensagem(json.mensagem, false);
            return;
        }

        if (entidade === 'fornecedor') {
            fornecedores = json.dados;
        }

        if (json.dados.length === 0) {
            corpo.innerHTML = `<tr><td colspan="${colunas}" class="text-center text-muted">Nada cadastrado ainda.</td></tr>`;
            return;
        }

        corpo.innerHTML = json.dados.map(linha => `
            <tr data-id="${linha.id}" data-entidade="${entidade}">
                ${config[entidade].map(def => montarCelula(def, linha)).join('')}
                <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-success btn-salvar">Salvar</button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-excluir">Excluir</button>
                </td>
            </tr>`).join('');
    } catch (erro) {
        mostrarMensagem('Não foi possível carregar os dados.', false);
    }
}

// envia um POST pro api.php e devolve o JSON
async function enviar(dados) {
    try {
        const resp = await fetch('api.php', { method: 'POST', body: dados });
        return await resp.json();
    } catch (erro) {
        return { ok: false, mensagem: 'Erro de comunicação com o servidor.' };
    }
}

// um único "ouvinte" de clique para todos os botões (event delegation),
// já que as linhas são criadas dinamicamente
document.addEventListener('click', async (evento) => {
    const botao = evento.target;
    const linha = botao.closest('tr[data-id]');
    if (!linha) return;

    const entidade = linha.dataset.entidade;
    const dados = new FormData();
    dados.append('entidade', entidade);
    dados.append('id', linha.dataset.id);

    if (botao.classList.contains('btn-salvar')) {
        dados.append('acao', 'atualizar');
        linha.querySelectorAll('[data-campo]').forEach(campo => dados.append(campo.dataset.campo, campo.value));
    } else if (botao.classList.contains('btn-excluir')) {
        if (!confirm('Tem certeza que deseja excluir?')) return;
        dados.append('acao', 'excluir');
    } else {
        return;
    }

    const json = await enviar(dados);
    mostrarMensagem(json.mensagem, json.ok);

    if (!json.ok) return;

// DEPOIS DE SALVAR OU EXCLUIR, RECARREGAR A TABELA QUE FOI ALTERADA (FORNECEDOR, PRODUTO OU CESTA)
    
    if (entidade === 'fornecedor') {
        await carregar('fornecedor');
        carregar('produto');          // o nome do fornecedor aparece nos produtos
    } else if (botao.classList.contains('btn-excluir')) {
        carregar(entidade);
    }
});

// ao abrir a página: fornecedores primeiro (os produtos precisam deles pro select)
(async () => {
    await carregar('fornecedor');
    carregar('produto');
    carregar('cesta');
})();
