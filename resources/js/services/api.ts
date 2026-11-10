const METODOS_SEGUROS = ['GET', 'HEAD', 'OPTIONS'];

function obterValorCookie(nome: string): string | null
{
    const prefixo = `${nome}=`;
    const cookie = document.cookie.split('; ').find((item) => item.startsWith(prefixo));
    return cookie ? decodeURIComponent(cookie.substring(prefixo.length)) : null;
}

async function renovarCookieCsrf(): Promise<void> {
    const resposta = await fetch('/sanctum/csrf-cookie', {
        headers: {
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    });

    if (!resposta.ok)
    {
        throw new Error('Não foi possível preparar a proteção da sessão.');
    }
}

export async function requisicaoApi( url: string, opcoes: RequestInit = {},): Promise<Response> {
    const cabecalhos = new Headers(opcoes.headers);
    const metodo = (opcoes.method ?? 'GET').toUpperCase();

    cabecalhos.set('Accept', 'application/json');

    if (!METODOS_SEGUROS.includes(metodo))
    {
        await renovarCookieCsrf();
        const tokenCsrf = obterValorCookie('XSRF-TOKEN');

        if (!tokenCsrf)
        {
            throw new Error('Token CSRF não encontrado.');
        }

        cabecalhos.set('X-XSRF-TOKEN', tokenCsrf);
    }

    // return fetch(url, {...opcoes, headers: cabecalhos,credentials: 'same-origin',});

    const resposta = await fetch(url, {
        ...opcoes,
        headers: cabecalhos,
        credentials: 'same-origin',
    });

    if ((resposta.status === 401 || resposta.status === 419) && window.location.pathname !== '/') {
        window.dispatchEvent(new Event('sessao-expirada'));
    }

    return resposta;
}
