<?php

namespace App\Enums;

enum CategoriaGastoEnum: string
{
    case Arriendo        = 'ARRIENDO';
    case Empleados       = 'EMPLEADOS';
    case ServiciosPublicos = 'SERVICIOS_PUBLICOS';
    case Mantenimiento   = 'MANTENIMIENTO';
    case Transporte      = 'TRANSPORTE';
    case Publicidad      = 'PUBLICIDAD';
    case Impuestos       = 'IMPUESTOS';
    case Otros           = 'OTROS';
    case Surtido         = 'SURTIDO';

    public function label(): string
    {
        return match($this) {
            self::Arriendo          => 'Arriendo',
            self::Empleados         => 'Empleados',
            self::ServiciosPublicos => 'Servicios Públicos',
            self::Mantenimiento     => 'Mantenimiento',
            self::Transporte        => 'Transporte',
            self::Publicidad        => 'Publicidad',
            self::Impuestos         => 'Impuestos',
            self::Otros             => 'Otros',
            self::Surtido           => 'Surtido',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Arriendo          => 'primary',
            self::Empleados         => 'success',
            self::ServiciosPublicos => 'info',
            self::Mantenimiento     => 'warning',
            self::Transporte        => 'secondary',
            self::Publicidad        => 'danger',
            self::Impuestos         => 'dark',
            self::Otros             => 'light',
            self::Surtido           => 'warning',
        };
    }
}
