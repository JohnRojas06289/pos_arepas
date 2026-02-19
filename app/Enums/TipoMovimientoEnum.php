<?php

namespace App\Enums;

enum TipoMovimientoEnum: string
{
    case Venta = 'VENTA';
    case Retiro = 'RETIRO';
    case Ingreso = 'INGRESO';
}
