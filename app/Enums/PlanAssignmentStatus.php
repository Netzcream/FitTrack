<?php

namespace App\Enums;

enum PlanAssignmentStatus: string
{
    case PENDING = 'pending';    // Futuro: starts_at > hoy
    case ACTIVE = 'active';       // Vigente: starts_at <= hoy <= ends_at
    case COMPLETED = 'completed'; // Finalizado normalmente
    case CANCELLED = 'cancelled'; // Cancelado/Reemplazado antes de tiempo

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Próximo',
            self::ACTIVE => 'Activo',
            self::COMPLETED => 'Completado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'amber',
            self::ACTIVE => 'green',
            self::COMPLETED => 'blue',
            self::CANCELLED => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'clock',
            self::ACTIVE => 'play-circle',
            self::COMPLETED => 'check-circle',
            self::CANCELLED => 'x-circle',
        };
    }
}
