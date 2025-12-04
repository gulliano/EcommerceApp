<?php

namespace App\Enums;

/**
 * Enum pour gérer les différents rôles utilisateurs
 * 
 * - ADMIN : Accès complet au panel d'administration
 * - CUSTOMER : Accès au panel client (panier, commandes)
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';

    /**
     * Obtient le label lisible du rôle en français
     * 
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::CUSTOMER => 'Client',
        };
    }

    /**
     * Vérifie si le rôle est administrateur
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Vérifie si le rôle est client
     * 
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    /**
     * Retourne tous les rôles disponibles sous forme de tableau
     * Utile pour les formulaires de sélection
     * 
     * @return array
     */
    public static function toArray(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::CUSTOMER->value => self::CUSTOMER->label(),
        ];
    }
}