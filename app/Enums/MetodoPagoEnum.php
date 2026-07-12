<?php

namespace App\Enums;

enum MetodoPagoEnum: string
{
    case Efectivo = 'EFECTIVO';
    case Nequi = 'NEQUI';
    case Daviplata = 'DAVIPLATA';
    case Bold = 'BOLD';
    case Fiado = 'FIADO';
    case Mixto = 'MIXTO';
}
