import 'bootstrap';
import type { RespostaErroApi, RespostaLogin, UsuarioAutenticado,} from './interfaces/Auth';
import type { RespostaListaPedidos } from './interfaces/Order';
import { carregarDetalhesPedido } from './pages/detalhesPedido';
import { carregarPedidos } from './pages/pedidos';
import { carregarProdutos } from './pages/produtos';
import { requisicaoApi } from './services/api';

const formularioLogin = document.querySelector<HTMLFormElement>('#login-form');
const campoEmail = document.querySelector<HTMLInputElement>('#login-email');
const campoSenha = document.querySelector<HTMLInputElement>('#login-password');
const areaFeedback = document.querySelector<HTMLDivElement>('#login-feedback');
const paginaProdutos = document.querySelector<HTMLElement>('#products-page');
const paginaPedidos = document.querySelector<HTMLElement>('#orders-page');
const paginaDetalhesPedido = document.querySelector<HTMLElement>('#order-details-page',);
const botaoSair = document.querySelector<HTMLButtonElement>('#logout-button');
const marcaAplicacao = document.querySelector<HTMLElement>('#application-brand');
const areaUsuarioAutenticado = document.querySelector<HTMLElement>('#authenticated-user',);
const nomeUsuarioAutenticado = document.querySelector<HTMLElement>('#authenticated-user-name',);
const interfaceAplicacao = document.querySelector<HTMLElement>('#application-shell',);
const camadaSessaoExpirada = document.querySelector<HTMLDivElement>('#session-expired-overlay',);

let sessaoExpiradaEmAndamento = false;

function exibirFeedback(mensagem: string, tipo: 'success' | 'danger'): void
{
    if (!areaFeedback)
    {
        return;
    }

    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} mb-0`;
    alerta.setAttribute('role', 'alert');
    alerta.textContent = mensagem;

    areaFeedback.replaceChildren(alerta);
}

async function direcionarAposLogin(): Promise<void>
{
    try
    {
        const resposta = await requisicaoApi('/api/orders');

        if (!resposta.ok)
        {
            window.location.assign('/produtos');
            return;
        }

        const dados = await resposta.json() as RespostaListaPedidos;

        if (dados.data.length > 0)
        {
            window.location.assign('/meus-pedidos');
            return;
        }

        window.location.assign('/produtos');
    }
    catch
    {
        window.location.assign('/produtos');
    }
}

async function obterUsuarioAutenticado(): Promise<UsuarioAutenticado | null>
{
    try {
            const resposta = await requisicaoApi('/api/user');

            if (!resposta.ok)
            {
                return null;
            }

            return await resposta.json() as UsuarioAutenticado;
    }
    catch
    {
        return null;
    }
}

function exibirUsuarioAutenticado(usuario: UsuarioAutenticado): void
{
    if (!marcaAplicacao || !areaUsuarioAutenticado || !nomeUsuarioAutenticado)
    {
        return;
    }

    nomeUsuarioAutenticado.textContent = usuario.name;
    marcaAplicacao.classList.add('d-none');
    areaUsuarioAutenticado.classList.remove('d-none');
    areaUsuarioAutenticado.classList.add('d-inline-flex');
    botaoSair?.classList.remove('d-none');
}

function tratarSessaoExpirada(): void
{
    if (sessaoExpiradaEmAndamento)
    {
        return;
    }

    sessaoExpiradaEmAndamento = true;
    interfaceAplicacao?.setAttribute('inert', '');
    camadaSessaoExpirada?.classList.remove('d-none');
    camadaSessaoExpirada?.classList.add('d-flex');
    window.setTimeout(() => {
        window.location.replace('/');
    }, 2000);
}

if (formularioLogin && campoEmail && campoSenha)
{
    formularioLogin.addEventListener('submit', async (evento) =>
    {
        evento.preventDefault();

        const botaoEntrar = formularioLogin.querySelector<HTMLButtonElement>( 'button[type="submit"]',);

        if (botaoEntrar)
        {
            botaoEntrar.disabled = true;
            botaoEntrar.textContent = 'Entrando...';
        }

        try
        {
            const resposta = await requisicaoApi('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: campoEmail.value,
                    password: campoSenha.value,
                }),
            });

            const dados = await resposta.json() as RespostaLogin | RespostaErroApi;

            if (!resposta.ok)
            {
                const erro = dados as RespostaErroApi;
                exibirFeedback(erro.message, 'danger');
                return;
            }

            void direcionarAposLogin();

        }
        catch
        {
            exibirFeedback('Não foi possível conectar à API. Tente novamente.','danger', );
        }
        finally
        {
            if (botaoEntrar)
            {
                botaoEntrar.disabled = false;
                botaoEntrar.textContent = 'Entrar';
            }
        }
    });
}

async function inicializarPaginaProtegida(): Promise<void>
{
    const usuario = await obterUsuarioAutenticado();

    if (!usuario)
    {
        window.location.replace('/');
        return;
    }

    exibirUsuarioAutenticado(usuario);

    if (paginaProdutos)
    {
        void carregarProdutos();
    }
    else if (paginaPedidos)
    {
        void carregarPedidos();

    }
    else if (paginaDetalhesPedido)
    {
        void carregarDetalhesPedido();
    }
}

if (paginaProdutos || paginaPedidos || paginaDetalhesPedido)
{
    void inicializarPaginaProtegida();
}

async function realizarLogout(): Promise<void>
{
    if (!botaoSair)
    {
        return;
    }

    botaoSair.disabled = true;
    botaoSair.textContent = 'Saindo...';

    try
    {
        const resposta = await requisicaoApi('/logout', { method: 'POST',});

        if (!resposta.ok)
        {
            throw new Error('Não foi possível encerrar a sessão.');
        }

        window.location.replace('/');
    }
    catch
    {
        window.alert('Não foi possível encerrar a sessão. Tente novamente.');
        botaoSair.disabled = false;
        botaoSair.textContent = 'Sair';
    }
}

botaoSair?.addEventListener('click', () => { void realizarLogout(); });
window.addEventListener('sessao-expirada', tratarSessaoExpirada);
