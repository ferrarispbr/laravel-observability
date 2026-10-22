    import 'bootstrap';
    import type {RespostaErroApi,RespostaLogin,UsuarioAutenticado,} from './interfaces/Auth';
    import {obterTokenAutenticacao,requisicaoApi,} from './services/api';
    import { carregarProdutos } from './pages/produtos';
    import { carregarPedidos } from './pages/pedidos';
    import { carregarDetalhesPedido } from './pages/detalhesPedido';
    import type { RespostaListaPedidos } from './interfaces/Order';

    const formularioLogin       =   document.querySelector<HTMLFormElement>('#login-form');
    const campoEmail            =   document.querySelector<HTMLInputElement>('#login-email');
    const campoSenha            =   document.querySelector<HTMLInputElement>('#login-password');
    const areaFeedback          =   document.querySelector<HTMLDivElement>('#login-feedback');
    const paginaProdutos        =   document.querySelector<HTMLElement>('#products-page');
    const paginaPedidos         =   document.querySelector<HTMLElement>('#orders-page');
    const paginaDetalhesPedido  =   document.querySelector<HTMLElement>('#order-details-page');
    const botaoSair             =   document.querySelector<HTMLButtonElement>('#logout-button');
    const marcaAplicacao        =   document.querySelector<HTMLElement>('#application-brand');
    const areaUsuarioAutenticado=   document.querySelector<HTMLElement>('#authenticated-user',);
    const nomeUsuarioAutenticado= document.querySelector<HTMLElement>('#authenticated-user-name',);


    function exibirFeedback(mensagem: string,tipo: 'success' | 'danger',): void
    {
        if (!areaFeedback) {
            return;
        }

        const alerta = document.createElement('div');

        alerta.className = `alert alert-${tipo} mb-0`;
        alerta.setAttribute('role', 'alert');
        alerta.textContent = mensagem;
        areaFeedback.replaceChildren(alerta);
    }

    async function direcionarAposLogin(): Promise<void> {
        try {
            const resposta = await requisicaoApi('/api/orders');

            if (!resposta.ok) {
                window.location.assign('/produtos');

                return;
            }

            const dados = await resposta.json() as RespostaListaPedidos;

            if (dados.data.length > 0) {
                window.location.assign('/meus-pedidos');

                return;
            }

            window.location.assign('/produtos');
        } catch {
            window.location.assign('/produtos');
        }
    }

    function exibirUsuarioAutenticado(): void
    {
        const usuarioArmazenado = sessionStorage.getItem('authenticated_user');

        if (!usuarioArmazenado || !marcaAplicacao || !areaUsuarioAutenticado || !nomeUsuarioAutenticado)
        {
            return;
        }

        try {
            const usuario = JSON.parse(usuarioArmazenado) as UsuarioAutenticado;

            nomeUsuarioAutenticado.textContent = usuario.name;

            marcaAplicacao.classList.add('d-none');
            areaUsuarioAutenticado.classList.remove('d-none');
            areaUsuarioAutenticado.classList.add('d-inline-flex');
            botaoSair?.classList.remove('d-none');
        } catch {
            // Se o conteúdo salvo for inválido, mantém a marca padrão visível.
        }
    }

    if (formularioLogin && campoEmail && campoSenha) {
        formularioLogin.addEventListener('submit', async (evento) => {
            evento.preventDefault();

            const botaoEntrar = formularioLogin.querySelector<HTMLButtonElement>(
                'button[type="submit"]',
            );

            if (botaoEntrar) {
                botaoEntrar.disabled = true;
                botaoEntrar.textContent = 'Entrando...';
            }

            try {
                const resposta = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: campoEmail.value,
                        password: campoSenha.value,
                    }),
                });

                const dados = await resposta.json() as RespostaLogin | RespostaErroApi;

                if (!resposta.ok) {
                    const erro = dados as RespostaErroApi;
                    exibirFeedback(erro.message, 'danger');
                    return;
                }

                const login = dados as RespostaLogin;

                sessionStorage.setItem('auth_token', login.token);
                sessionStorage.setItem('authenticated_user', JSON.stringify(login.user), );

                void direcionarAposLogin();

            } catch {
                exibirFeedback('Não foi possível conectar à API. Tente novamente.','danger',);
            } finally {
                if (botaoEntrar) {
                    botaoEntrar.disabled = false;
                    botaoEntrar.textContent = 'Entrar';
                }
            }
        });
    }

    if (paginaProdutos || paginaPedidos || paginaDetalhesPedido)
    {
        if (!obterTokenAutenticacao())
        {
            window.location.replace('/');
        }
        else
        {
            exibirUsuarioAutenticado();

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
    }

    async function realizarLogout(): Promise<void> {
        if (!botaoSair) {
            return;
        }

        botaoSair.disabled = true;
        botaoSair.textContent = 'Saindo...';

        try {
            await requisicaoApi('/api/logout', {
                method: 'POST',
            });
        } finally {
            sessionStorage.removeItem('auth_token');
            sessionStorage.removeItem('authenticated_user');

            window.location.replace('/');
        }
    }

    botaoSair?.addEventListener('click', () => {
        void realizarLogout();
    });
