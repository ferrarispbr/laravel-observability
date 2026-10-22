
import type {RespostaErroApi,UsuarioPerfil,} from '../interfaces/Auth';
import type { Pedido, RespostaPedido } from '../interfaces/Order';
import { requisicaoApi } from '../services/api';
import { obterClasseStatus } from '../utils/statusPedido';

const carregamentoPedido=   document.querySelector<HTMLDivElement>('#order-loading',);
const conteudoPedido    =   document.querySelector<HTMLDivElement>('#order-content',);
const feedbackPedido    =   document.querySelector<HTMLDivElement>('#order-feedback',);


function formatarPreco(preco: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(preco));
}

function formatarData(data: string): string {
    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(data));
}

// function obterClasseStatus(status: Pedido['status']): string {

//     const classesPorStatus = {
//         pendente: 'border border-warning text-warning bg-transparent',
//         confirmado: 'border border-success text-success bg-transparent',
//         cancelado: 'border border-danger text-danger bg-transparent',
//     };

//     return classesPorStatus[status];
// }

function obterIdPedidoDaUrl(): number | null {
    const partesDaUrl = window.location.pathname.split('/');
    const ultimoTrecho = partesDaUrl.at(-1);
    const pedidoId = Number(ultimoTrecho);

    if (!Number.isInteger(pedidoId) || pedidoId <= 0) {
        return null;
    }

    return pedidoId;
}

function preencherTexto(seletor: string, valor: string): void {
    const elemento = document.querySelector<HTMLElement>(seletor);

    if (elemento) {
        elemento.textContent = valor;
    }
}

function exibirFeedbackPedido(mensagem: string,tipo: 'success' | 'danger',): void
{
    if (!feedbackPedido) {
        return;
    }

    const alerta = document.createElement('div');

    alerta.className = `alert alert-${tipo} mb-4`;
    alerta.setAttribute('role', 'alert');
    alerta.textContent = mensagem;

    feedbackPedido.replaceChildren(alerta);
}

function preencherDadosPedido(pedido: Pedido): void {
    preencherTexto('#order-number', `#${pedido.id}`);
    preencherTexto('#order-date', formatarData(pedido.created_at));

    const status = document.querySelector<HTMLSpanElement>('#order-status');

    if (status) {
        status.className = `badge ${obterClasseStatus(pedido.status)}`;
        status.textContent = pedido.status;
    }
}

function preencherDadosUsuario(usuario: UsuarioPerfil): void {
    preencherTexto('#user-name', usuario.name);
    preencherTexto(
        '#user-address',
        `${usuario.address}, ${usuario.address_number}`,
    );
    preencherTexto('#user-zip-code', usuario.zip_code);
    preencherTexto('#user-neighborhood', usuario.neighborhood);
    preencherTexto('#user-city', usuario.city);
    preencherTexto('#user-state', usuario.state);

    const containerComplemento = document.querySelector<HTMLDivElement>(
        '#user-complement-container',
    );

    if (usuario.address_complement) {
        preencherTexto('#user-complement', usuario.address_complement);
        containerComplemento?.classList.remove('d-none');
    } else {
        containerComplemento?.classList.add('d-none');
    }
}

function preencherItensPedido(pedido: Pedido): void {
    const itensPedido = document.querySelector<HTMLTableSectionElement>(
        '#order-items',
    );

    if (!itensPedido) {
        return;
    }

    itensPedido.replaceChildren();

    pedido.items.forEach((item, indice) => {
        const linha = document.createElement('tr');
        const numero = document.createElement('td');
        const produto = document.createElement('td');
        const valorUnitario = document.createElement('td');
        const quantidade = document.createElement('td');
        const valorTotal = document.createElement('td');

        numero.textContent = String(indice + 1);
        produto.textContent = item.product.name;

        valorUnitario.className = 'text-end';
        valorUnitario.textContent = formatarPreco(item.unit_price);

        quantidade.className = 'text-center';
        quantidade.textContent = String(item.quantity);

        valorTotal.className = 'text-end';
        valorTotal.textContent = formatarPreco(
            Number(item.unit_price) * item.quantity,
        );

        linha.append(
            numero,
            produto,
            valorUnitario,
            quantidade,
            valorTotal,
        );

        itensPedido.append(linha);
    });

    preencherTexto('#order-total', formatarPreco(pedido.total));
}

function criarLinksNavegacao(): DocumentFragment
{
    const navegacao             =   document.createDocumentFragment();
    const linkContinuarComprando=   document.createElement('a');
    const linkMeusPedidos       =   document.createElement('a');

    linkContinuarComprando.href         =   '/produtos';
    linkContinuarComprando.className    =   'btn btn-primary flex-fill';
    linkContinuarComprando.textContent  =   'Continuar comprando';

    linkMeusPedidos.href        =   '/meus-pedidos';
    linkMeusPedidos.className   =   'btn btn-outline-secondary flex-fill';
    linkMeusPedidos.textContent =   'Ver meus pedidos';

    navegacao.append(linkContinuarComprando, linkMeusPedidos);

    return navegacao;
}

function criarAcoesPedido(pedido: Pedido): void {
    const acoesPedido = document.querySelector<HTMLDivElement>(
        '#order-actions',
    );

    if (!acoesPedido) {
        return;
    }

    acoesPedido.replaceChildren();
    acoesPedido.className = 'd-flex flex-column flex-md-row gap-2';

    if (pedido.status === 'cancelado')
    {
        acoesPedido.append(criarLinksNavegacao());
        return;
    }

    const botaoConfirmar= document.createElement('button');
    const botaoCancelar = document.createElement('button');

    botaoCancelar.type          =   'button';
    botaoCancelar.className     =   'btn btn-outline-danger flex-fill';
    botaoCancelar.textContent   =   'Cancelar pedido';

    botaoCancelar.addEventListener('click', () => {
        void cancelarPedido(pedido, botaoCancelar);
    });

    botaoConfirmar.type         =   'button';
    botaoConfirmar.className    =   'btn btn-success flex-fill';
    botaoConfirmar.textContent  =   'Confirmar pedido';

    botaoConfirmar.addEventListener('click', () => {
        void confirmarPedido(pedido, botaoConfirmar);
    });

    acoesPedido.append(botaoCancelar);

    if (pedido.status === 'pendente')
    {
        acoesPedido.append(botaoConfirmar);
    }

    if (pedido.status === 'confirmado')
    {
        acoesPedido.append(criarLinksNavegacao());
    }
}

async function confirmarPedido(pedido: Pedido,botaoConfirmar: HTMLButtonElement,): Promise<void>
{
    botaoConfirmar.disabled = true;
    botaoConfirmar.textContent = 'Confirmando...';

    try {
        const resposta = await requisicaoApi(
            `/api/orders/${pedido.id}/confirm`,
            {
                method: 'POST',
            },
        );

        const dados = await resposta.json() as RespostaPedido | RespostaErroApi;

        if (!resposta.ok) {
            const erro = dados as RespostaErroApi;

            throw new Error(erro.message);
        }

        const pedidoConfirmado = dados as RespostaPedido;

        preencherDadosPedido(pedidoConfirmado.data);
        preencherItensPedido(pedidoConfirmado.data);
        criarAcoesPedido(pedidoConfirmado.data);

        exibirFeedbackPedido(pedidoConfirmado.message, 'success');
    } catch (erro) {
        const mensagem = erro instanceof Error
            ? erro.message
            : 'Não foi possível confirmar o pedido. Tente novamente.';

        exibirFeedbackPedido(mensagem, 'danger');

        botaoConfirmar.disabled = false;
        botaoConfirmar.textContent = 'Confirmar pedido';
    }
}

async function cancelarPedido(pedido: Pedido,botaoCancelar: HTMLButtonElement,): Promise<void>
{
    botaoCancelar.disabled = true;
    botaoCancelar.textContent = 'Cancelando...';

    try {
        const resposta = await requisicaoApi(
            `/api/orders/${pedido.id}/cancel`,
            {
                method: 'POST',
            },
        );

        const dados = await resposta.json() as RespostaPedido | RespostaErroApi;

        if (!resposta.ok) {
            const erro = dados as RespostaErroApi;

            throw new Error(erro.message);
        }

        const pedidoCancelado = dados as RespostaPedido;

        preencherDadosPedido(pedidoCancelado.data);
        preencherItensPedido(pedidoCancelado.data);
        criarAcoesPedido(pedidoCancelado.data);

        exibirFeedbackPedido(pedidoCancelado.message, 'success');
    } catch (erro) {
        const mensagem = erro instanceof Error
            ? erro.message
            : 'Não foi possível cancelar o pedido. Tente novamente.';

        exibirFeedbackPedido(mensagem, 'danger');

        botaoCancelar.disabled = false;
        botaoCancelar.textContent = 'Cancelar pedido';
    }
}

export async function carregarDetalhesPedido(): Promise<void>
{
    const pedidoId = obterIdPedidoDaUrl();

    if (!pedidoId)
    {
        exibirFeedbackPedido('Não foi possível identificar o pedido.', 'danger');
        return;
    }

    feedbackPedido?.replaceChildren();

    try {
        const [respostaPedido, respostaUsuario] = await Promise.all([
            requisicaoApi(`/api/orders/${pedidoId}`),
            requisicaoApi('/api/user'),
        ]);

        if (!respostaPedido.ok || !respostaUsuario.ok)
        {
            throw new Error('Não foi possível carregar os detalhes do pedido.');
        }

        const dadosPedido   =   await respostaPedido.json() as RespostaPedido;
        const usuario       =   await respostaUsuario.json() as UsuarioPerfil;

        preencherDadosPedido(dadosPedido.data);
        preencherDadosUsuario(usuario);
        preencherItensPedido(dadosPedido.data);
        criarAcoesPedido(dadosPedido.data);

        carregamentoPedido?.classList.add('d-none');
        conteudoPedido?.classList.remove('d-none');

    } catch {
        carregamentoPedido?.classList.add('d-none');
        exibirFeedbackPedido('Não foi possível carregar os detalhes do pedido. Tente novamente.','danger');
    }
}
