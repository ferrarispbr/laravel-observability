import type {Pedido,RespostaListaPedidos,} from '../interfaces/Order';
import { requisicaoApi } from '../services/api';

const listaPedidos      =   document.querySelector<HTMLDivElement>('#orders-list');
const feedbackPedidos   =   document.querySelector<HTMLDivElement>('#orders-feedback',);

function formatarPreco(preco: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(preco));
}

function obterClasseStatus(status: Pedido['status']): string {
    const classesPorStatus = {
        pendente: 'text-bg-warning',
        confirmado: 'text-bg-success',
        cancelado: 'text-bg-secondary',
    };

    return classesPorStatus[status];
}

function exibirFeedbackPedidos(mensagem: string): void {
    if (!feedbackPedidos) {
        return;
    }

    const alerta = document.createElement('div');

    alerta.className = 'alert alert-danger mb-3';
    alerta.setAttribute('role', 'alert');
    alerta.textContent = mensagem;

    feedbackPedidos.replaceChildren(alerta);
}

function exibirCarregamentoPedidos(): void {
    if (!listaPedidos) {
        return;
    }

    const carregamento = document.createElement('div');
    const spinner = document.createElement('span');
    const mensagem = document.createElement('span');

    carregamento.className = 'd-flex align-items-center gap-2 text-body-secondary';

    spinner.className = 'spinner-border spinner-border-sm';
    spinner.setAttribute('role', 'status');
    spinner.setAttribute('aria-hidden', 'true');

    mensagem.textContent = 'Carregando pedidos...';

    carregamento.append(spinner, mensagem);

    listaPedidos.replaceChildren(carregamento);
}

function criarResumoPedido(pedido: Pedido): HTMLElement {
    const card          =   document.createElement('article');
    const corpo         =   document.createElement('div');
    const linha         =   document.createElement('div');
    const informacoes   =   document.createElement('div');
    const titulo        =   document.createElement('h2');
    const status        =   document.createElement('span');
    const total         =   document.createElement('p');
    const linkDetalhes  =   document.createElement('a');

    card.className = 'card mb-3';
    corpo.className = 'card-body';
    linha.className = 'd-flex justify-content-between align-items-start gap-3';

    titulo.className = 'h6 mb-2';
    titulo.textContent = `Pedido #${pedido.id}`;

    status.className = `badge ${obterClasseStatus(pedido.status)}`;
    status.textContent = pedido.status;

    total.className = 'mb-0 text-body-secondary';
    total.textContent = `Total: ${formatarPreco(pedido.total)}`;

    informacoes.append(titulo, total);

    linkDetalhes.href = `/pedidos/${pedido.id}`;
    linkDetalhes.className = 'btn btn-outline-primary btn-sm';
    linkDetalhes.textContent = 'Ver detalhes';

    linha.append(informacoes, status);
    corpo.append(linha, linkDetalhes);
    card.append(corpo);

    return card;
}

export async function carregarPedidos(): Promise<void> {
    if (!listaPedidos) {
        return;
    }

    exibirCarregamentoPedidos();

    feedbackPedidos?.replaceChildren();

    try {
        const resposta = await requisicaoApi('/api/orders');

        if (!resposta.ok) {
            throw new Error('Não foi possível carregar os pedidos.');
        }

        const dados = await resposta.json() as RespostaListaPedidos;

        listaPedidos.replaceChildren();

        if (dados.data.length === 0) {
            const mensagemVazia = document.createElement('p');

            mensagemVazia.className = 'text-body-secondary mb-0';
            mensagemVazia.textContent = 'Você ainda não possui pedidos criados.';

            listaPedidos.append(mensagemVazia);

            return;
        }

        dados.data.forEach((pedido) => {
            listaPedidos.append(criarResumoPedido(pedido));
        });
    } catch {
        exibirFeedbackPedidos(
            'Não foi possível carregar os pedidos. Tente novamente.',
        );
    }
}
