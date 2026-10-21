    import 'bootstrap';
    import type {RespostaErroApi,RespostaLogin,} from './interfaces/Auth';
    import {obterTokenAutenticacao,requisicaoApi,} from './services/api';
    import { carregarProdutos } from './pages/produtos';
    import { carregarPedidos } from './pages/pedidos';
    import { carregarDetalhesPedido } from './pages/detalhesPedido';

    const formularioLogin       =   document.querySelector<HTMLFormElement>('#login-form');
    const campoEmail            =   document.querySelector<HTMLInputElement>('#login-email');
    const campoSenha            =   document.querySelector<HTMLInputElement>('#login-password');
    const areaFeedback          =   document.querySelector<HTMLDivElement>('#login-feedback');
    const paginaProdutos        =   document.querySelector<HTMLElement>('#products-page');
    const paginaPedidos         =   document.querySelector<HTMLElement>('#orders-page');
    const paginaDetalhesPedido  =   document.querySelector<HTMLElement>('#order-details-page');
    const botaoSair             =   document.querySelector<HTMLButtonElement>('#logout-button');

    function exibirFeedback(
        mensagem: string,
        tipo: 'success' | 'danger',
    ): void {
        if (!areaFeedback) {
            return;
        }

        const alerta = document.createElement('div');

        alerta.className = `alert alert-${tipo} mb-0`;
        alerta.setAttribute('role', 'alert');
        alerta.textContent = mensagem;

        areaFeedback.replaceChildren(alerta);
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
                sessionStorage.setItem(
                    'authenticated_user',
                    JSON.stringify(login.user),
                );

                // exibirFeedback(login.message, 'success');
                window.location.assign('/produtos');

            } catch {
                exibirFeedback(
                    'Não foi possível conectar à API. Tente novamente.',
                    'danger',
                );
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
        else if (paginaProdutos)
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
