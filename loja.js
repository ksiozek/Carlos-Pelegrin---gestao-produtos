// Validação da tela de produtos: só deixa enviar se marcar o mínimo de produtos.

const form = document.getElementById('form-loja');

if (form) {
    const minimo = parseInt(form.dataset.minimo, 10);
    const contador = document.getElementById('contador');
    const aviso = document.getElementById('aviso-validacao');

    function contarMarcados() {
        return document.querySelectorAll('.check-produto:checked').length;
    }

    // atualiza o contador toda vez que marcar/desmarcar
    document.querySelectorAll('.check-produto').forEach(check => {
        check.addEventListener('change', () => {
            const qtd = contarMarcados();
            contador.textContent = qtd;
            contador.className = 'badge fs-6 ' + (qtd >= minimo ? 'bg-success' : 'bg-secondary');
            if (qtd >= minimo) aviso.classList.add('d-none');
        });
    });

    // bloqueia o envio se não atingiu o mínimo
    form.addEventListener('submit', (evento) => {
        const qtd = contarMarcados();
        if (qtd < minimo) {
            evento.preventDefault();
            aviso.textContent = `Selecione pelo menos ${minimo} produtos para adicionar à cesta (você marcou ${qtd}).`;
            aviso.classList.remove('d-none');
        }
    });
}
