export function obterTokenAutenticacao(): string | null {
    return sessionStorage.getItem('auth_token');
}

export async function requisicaoApi(
    url: string,
    opcoes: RequestInit = {},
): Promise<Response> {
    const cabecalhos = new Headers(opcoes.headers);
    const token = obterTokenAutenticacao();

    cabecalhos.set('Accept', 'application/json');

    if (token) {
        cabecalhos.set('Authorization', `Bearer ${token}`);
    }

    return fetch(url, {
        ...opcoes,
        headers: cabecalhos,
    });
}
