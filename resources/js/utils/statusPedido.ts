
import type { Pedido } from '../interfaces/Order';

export function obterClasseStatus(status: Pedido['status']): string {
    const classesPorStatus = {
        pendente: 'border border-warning text-warning bg-transparent',
        confirmado: 'border border-success text-success bg-transparent',
        cancelado: 'border border-danger text-danger bg-transparent',
    };

    return classesPorStatus[status];
}
