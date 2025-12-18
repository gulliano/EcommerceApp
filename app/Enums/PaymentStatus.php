<?php



namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::UNPAID => 'Non payé',
            self::PAID => 'Payé',
            self::REFUNDED => 'Remboursé',
            self::PARTIALLY_REFUNDED => 'Partiellement remboursé',
            self::FAILED => 'Échoué',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::UNPAID => 'warning',
            self::PAID => 'success',
            self::REFUNDED => 'danger',
            self::PARTIALLY_REFUNDED => 'info',
            self::FAILED => 'danger',
        };
    }
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}