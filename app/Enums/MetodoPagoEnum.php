<?php

namespace App\Enums;

enum MetodoPagoEnum: string
{
    case Efectivo = 'EFECTIVO';
    case Tarjeta = 'TARJETA';
    case Nequi = 'NEQUI';
    case Daviplata = 'DAVIPLATA';
}
