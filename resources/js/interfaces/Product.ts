export interface Produto {
    id: number;
    name: string;
    price: string;
    stock: number;
}

export interface RespostaListaProdutos {
    data: Produto[];
}
