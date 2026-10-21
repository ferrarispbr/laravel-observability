export interface UsuarioAutenticado {
    id: number;
    name: string;
    email: string;
}

export interface UsuarioPerfil extends UsuarioAutenticado {
    address: string;
    address_number: string;
    address_complement: string | null;
    neighborhood: string;
    city: string;
    state: string;
    zip_code: string;
}

export interface RespostaLogin {
    message: string;
    user: UsuarioAutenticado;
    token: string;
}

export interface RespostaErroApi {
    message: string;
    errors?: Record<string, string[]>;
}
