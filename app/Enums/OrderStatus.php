<?php

namespace App\Enums;

/**
 * Enum pour gérer les différents statuts de commande
 * 
 * Workflow typique :
 * PENDING → CONFIRMED → PROCESSING → SHIPPED → DELIVERED
 * ou
 * PENDING → CANCELLED
 */
enum OrderStatus: string
{
    case PENDING = 'pending';           // En attente de validation
    case CONFIRMED = 'confirmed';       // Confirmée par l'admin
    case PROCESSING = 'processing';     // En cours de préparation
    case SHIPPED = 'shipped';           // Expédiée
    case DELIVERED = 'delivered';       // Livrée
    case CANCELLED = 'cancelled';       // Annulée

    /**
     * Obtient le label lisible du statut en français
     * 
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::PROCESSING => 'En préparation',
            self::SHIPPED => 'Expédiée',
            self::DELIVERED => 'Livrée',
            self::CANCELLED => 'Annulée',
        };
    }

    /**
     * Obtient la couleur associée au statut (pour Filament)
     * 
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'success',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Obtient l'icône associée au statut (pour Filament)
     * 
     * @return string
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::PROCESSING => 'heroicon-o-cog',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    /**
     * Vérifie si le statut est modifiable
     * 
     * @return bool
     */
    public function isEditable(): bool
    {
        return !in_array($this, [self::DELIVERED, self::CANCELLED]);
    }

    /**
     * Retourne tous les statuts possibles
     * 
     * @return array
     */
    public static function toArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::CONFIRMED->value => self::CONFIRMED->label(),
            self::PROCESSING->value => self::PROCESSING->label(),
            self::SHIPPED->value => self::SHIPPED->label(),
            self::DELIVERED->value => self::DELIVERED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}