

import type {Produto,RespostaListaProdutos,} from '../interfaces/Product';
import type {ItemCarrinho,RequisicaoCriarPedido,RespostaCriarPedido,} from '../interfaces/Order';
import type { RespostaErroApi } from '../interfaces/Auth';
import { requisicaoApi } from '../services/api';

const listaProdutos     =   document.querySelector<HTMLDivElement>('#products-list');
const feedbackProdutos  =   document.querySelector<HTMLDivElement>('#products-feedback');
const itensCarrinho     =   document.querySelector<HTMLDivElement>('#cart-items');
const totalCarrinho     =   document.querySelector<HTMLSpanElement>('#cart-total');
const botaoCriarPedido  =   document.querySelector<HTMLButtonElement>('#create-order-button');
const feedbackPedido    =   document.querySelector<HTMLDivElement>('#order-feedback',);
const carrinho          =   new Map<number, ItemCarrinho>();

function exibirFeedbackProdutos(
    mensagem: string,
    tipo: 'danger' | 'secondary' | 'warning',
): void {
    if (!feedbackProdutos) {
        return;
    }

    const alerta = document.createElement('div');

    alerta.className = `alert alert-${tipo}`;
    alerta.setAttribute('role', 'alert');
    alerta.textContent = mensagem;

    feedbackProdutos.replaceChildren(alerta);
}

function exibirFeedbackPedido(
    mensagem: string,
    tipo: 'success' | 'danger',
): void {
    if (!feedbackPedido) {
        return;
    }

    const alerta = document.createElement('div');

    alerta.className = `alert alert-${tipo} mb-0`;
    alerta.setAttribute('role', 'alert');
    alerta.textContent = mensagem;

    feedbackPedido.replaceChildren(alerta);
}

function formatarPreco(preco: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(preco));
}

function calcularTotalCarrinho(): number {
    return Array.from(carrinho.values()).reduce(
        (total, item) => total + Number(item.produto.price) * item.quantidade,
        0,
    );
}

function atualizarCarrinho(): void {

    if (!itensCarrinho || !totalCarrinho || !botaoCriarPedido) {
        return;
    }

    itensCarrinho.replaceChildren();

    const produtosSelecionados = Array.from(carrinho.values());

    totalCarrinho.textContent = formatarPreco(calcularTotalCarrinho());
    botaoCriarPedido.disabled = produtosSelecionados.length === 0;

    if (produtosSelecionados.length === 0) {

        const mensagemVazia = document.createElement('p');

        mensagemVazia.className = 'text-body-secondary mb-0';
        mensagemVazia.textContent = 'Nenhum produto selecionado.';

        itensCarrinho.append(mensagemVazia);

        return;
    }

    produtosSelecionados.forEach((item) => {
        const linha         =   document.createElement('div');
        const descricao     =   document.createElement('div');
        const nome          =   document.createElement('div');
        const subtotal      =   document.createElement('small');
        const controles     =   document.createElement('div');
        const botaoDiminuir =   document.createElement('button');
        const quantidade    =   document.createElement('span');
        const botaoAumentar =   document.createElement('button');
        const acoes         =   document.createElement('div');
        const botaoRemover  =   document.createElement('button');

        linha.className = 'd-flex justify-content-between align-items-center gap-2 border-bottom pb-3 mb-3';
        controles.className = 'btn-group btn-group-sm';
        acoes.className = 'd-flex align-items-center gap-2';

        nome.className = 'fw-semibold';
        nome.textContent = item.produto.name;

        subtotal.className = 'text-body-secondary';
        subtotal.textContent = formatarPreco(
            Number(item.produto.price) * item.quantidade,
        );

        descricao.append(nome, subtotal);

        botaoDiminuir.type = 'button';
        botaoDiminuir.className = 'btn btn-outline-secondary';
        botaoDiminuir.textContent = '−';
        botaoDiminuir.setAttribute('aria-label',`Diminuir ${item.produto.name}`, );
        botaoDiminuir.disabled = item.quantidade === 1;

        botaoDiminuir.addEventListener('click', () => {
            alterarQuantidade(item.produto.id, -1);
        });


        quantidade.className = 'btn btn-outline-secondary disabled';
        quantidade.textContent = String(item.quantidade);

        botaoAumentar.type = 'button';
        botaoAumentar.className = 'btn btn-outline-secondary';
        botaoAumentar.textContent = '+';
        botaoAumentar.disabled = item.quantidade >= item.produto.stock;
        botaoAumentar.setAttribute('aria-label',`Aumentar ${item.produto.name}`,);

        botaoAumentar.addEventListener('click', () => {
            alterarQuantidade(item.produto.id, 1);
        });

        botaoRemover.type = 'button';
        botaoRemover.className = 'btn btn-sm btn-outline-danger';
        botaoRemover.textContent = 'Remover';

        botaoRemover.addEventListener('click', () => {
            carrinho.delete(item.produto.id);
            atualizarCarrinho();
        });

        controles.append(botaoDiminuir, quantidade, botaoAumentar);
        acoes.append(controles, botaoRemover);
        linha.append(descricao, acoes);
        itensCarrinho.append(linha);
    });
}

function adicionarAoCarrinho(produto: Produto): void {
    const itemExistente = carrinho.get(produto.id);

    if (itemExistente) {
        alterarQuantidade(produto.id, 1);

        return;
    }

    if (produto.stock === 0) {
        exibirFeedbackProdutos(
            'Este produto está sem estoque disponível.',
            'warning',
        );

        return;
    }

    carrinho.set(produto.id, {
        produto,
        quantidade: 1,
    });

    atualizarCarrinho();
}

function alterarQuantidade(
    produtoId: number,
    alteracao: number,
): void {
    const item = carrinho.get(produtoId);

    if (!item) {
        return;
    }

    const novaQuantidade = item.quantidade + alteracao;

    if (novaQuantidade <= 0) {
        carrinho.delete(produtoId);
        atualizarCarrinho();

        return;
    }

    if (novaQuantidade > item.produto.stock) {
        exibirFeedbackProdutos(
            `A quantidade de ${item.produto.name} não pode ser maior que o estoque disponível.`,
            'warning',
        );

        return;
    }

    carrinho.set(produtoId, {
        ...item,
        quantidade: novaQuantidade,
    });

    atualizarCarrinho();
}

async function criarPedido(): Promise<void> {
    const itens = Array.from(carrinho.values());

    if (itens.length === 0 || !botaoCriarPedido) {
        return;
    }

    const requisicao: RequisicaoCriarPedido = {
        items: itens.map((item) => ({
            product_id: item.produto.id,
            quantity: item.quantidade,
        })),
    };

    botaoCriarPedido.disabled = true;
    botaoCriarPedido.textContent = 'Criando pedido...';

    feedbackPedido?.replaceChildren();

    try {
        const resposta = await requisicaoApi('/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requisicao),
        });

        const dados = await resposta.json() as RespostaCriarPedido | RespostaErroApi;

        if (!resposta.ok)
        {
            const erro = dados as RespostaErroApi;
            throw new Error(erro.message);
        }

        const pedidoCriado = dados as RespostaCriarPedido;
        window.location.assign(`/pedidos/${pedidoCriado.data.id}`);

    }
    catch (erro)
    {
        const mensagem = erro instanceof Error ? erro.message : 'Não foi possível criar o pedido. Tente novamente.';
        exibirFeedbackPedido(mensagem, 'danger');
    }
    finally
    {
        botaoCriarPedido.textContent = 'Criar pedido';
        botaoCriarPedido.disabled = carrinho.size === 0;
    }
}

function criarCardProduto(produto: Produto): HTMLDivElement {
    const coluna = document.createElement('div');
    const card = document.createElement('article');
    const corpo = document.createElement('div');
    const nome = document.createElement('h3');
    const preco = document.createElement('p');
    const estoque = document.createElement('p');
    const botaoAdicionar = document.createElement('button');

    coluna.className = 'col-md-6';
    card.className = 'card h-100';
    corpo.className = 'card-body d-flex flex-column';

    nome.className = 'h6 card-title';
    nome.textContent = produto.name;

    preco.className = 'card-text fw-semibold mb-1';
    preco.textContent = formatarPreco(produto.price);

    estoque.className = 'card-text text-body-secondary mb-3';
    estoque.textContent = `Estoque disponível: ${produto.stock}`;

    botaoAdicionar.type = 'button';
    botaoAdicionar.className = 'btn btn-outline-primary mt-auto';
    botaoAdicionar.textContent = 'Adicionar';
    botaoAdicionar.disabled = produto.stock === 0;

    botaoAdicionar.addEventListener('click', () => {
        adicionarAoCarrinho(produto);
    });

    corpo.append(nome, preco, estoque, botaoAdicionar);
    card.append(corpo);
    coluna.append(card);

    return coluna;
}

export async function carregarProdutos(): Promise<void> {

    if (!listaProdutos) {
        return;
    }

    exibirCarregamentoProdutos();

    try {
        const resposta = await requisicaoApi('/api/products');

        if (!resposta.ok) {
            throw new Error('Não foi possível carregar os produtos.');
        }

        const dados = await resposta.json() as RespostaListaProdutos;
        listaProdutos.replaceChildren();

        if (dados.data.length === 0) {
            exibirFeedbackProdutos(
                'Nenhum produto cadastrado no momento.',
                'secondary',
            );

            return;
        }

        feedbackProdutos?.replaceChildren();

        dados.data.forEach((produto) => {
            listaProdutos.append(criarCardProduto(produto));
        });
    } catch {
        exibirFeedbackProdutos(
            'Não foi possível carregar os produtos. Tente novamente.',
            'danger',
        );
    }
}

function exibirCarregamentoProdutos(): void {
    if (!listaProdutos) {
        return;
    }

    const coluna = document.createElement('div');
    const carregamento = document.createElement('div');
    const spinner = document.createElement('span');
    const mensagem = document.createElement('span');

    coluna.className = 'col-12';

    carregamento.className = 'd-flex align-items-center gap-2 text-body-secondary';

    spinner.className = 'spinner-border spinner-border-sm';
    spinner.setAttribute('role', 'status');
    spinner.setAttribute('aria-hidden', 'true');

    mensagem.textContent = 'Carregando produtos...';

    carregamento.append(spinner, mensagem);
    coluna.append(carregamento);

    listaProdutos.replaceChildren(coluna);
}

botaoCriarPedido?.addEventListener('click', () => {
    void criarPedido();
});
