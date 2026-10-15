<?php

namespace App;

enum OrderStatus: string
{
    case PENDENTE = 'pendente';
    case CONFIRMADO = 'confirmado';
    case CANCELADO = 'cancelado';
}
