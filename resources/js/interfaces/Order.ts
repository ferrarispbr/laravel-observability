import type { Produto } from './Product';

export interface ItemCarrinho {
    produto: Produto;
    quantidade: number;
}

export interface ItemNovoPedido {
    product_id: number;
    quantity: number;
}

export interface RequisicaoCriarPedido {
    items: ItemNovoPedido[];
}

export interface RespostaCriarPedido {
    message: string;
}

export interface ItemPedido {
    id: number;
    product_id: number;
    quantity: number;
    unit_price: string;
    product: Produto;
}

export interface Pedido {
    id: number;
    status: 'pendente' | 'confirmado' | 'cancelado';
    total: string;
    created_at: string;
    items: ItemPedido[];
}

export interface RespostaListaPedidos {
    data: Pedido[];
}

export interface RespostaPedido {
    message: string;
    data: Pedido;
}
