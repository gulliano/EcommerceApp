<?php

namespace App\Models;

use App\Models\User;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
        'customer_notes',
        'admin_notes',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ==========================================
    // EVENTS & OBSERVERS
    // ==========================================

    /**
     * Boot du modèle pour auto-génération
     */
    protected static function boot()
    {
        parent::boot();

        // Génère automatiquement le numéro de commande
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Génère un numéro de commande unique
     * Format : ORD-YYYYMMDD-XXXXX
     * 
     * @return string
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5));
        
        $orderNumber = "ORD-{$date}-{$random}";
        
        // Vérifie l'unicité (très rare collision mais on vérifie quand même)
        while (self::where('order_number', $orderNumber)->exists()) {
            $random = strtoupper(Str::random(5));
            $orderNumber = "ORD-{$date}-{$random}";
        }
        
        return $orderNumber;
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Une commande appartient à un utilisateur
     * Relation Many-to-One
     * 
     * Usage : $order->user
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une commande contient plusieurs articles
     * Relation One-to-Many
     * 
     * Usage : $order->items
     * 
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope pour filtrer par statut
     * 
     * Usage : Order::withStatus(OrderStatus::PENDING)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param OrderStatus $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour obtenir les commandes en attente
     * 
     * Usage : Order::pending()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    // ==========================================
    // MÉTHODES DE CHANGEMENT DE STATUT
    // ==========================================

    /**
     * Confirme la commande
     * 
     * @return bool
     */
    public function confirm(): bool
    {
        if ($this->status !== OrderStatus::PENDING) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Marque la commande comme en cours de préparation
     * 
     * @return bool
     */
    public function markAsProcessing(): bool
    {
        if (!in_array($this->status, [OrderStatus::CONFIRMED])) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    /**
     * Marque la commande comme expédiée
     * 
     * @return bool
     */
    public function ship(): bool
    {
        if (!in_array($this->status, [OrderStatus::CONFIRMED, OrderStatus::PROCESSING])) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::SHIPPED,
            'shipped_at' => now(),
        ]);
    }

    /**
     * Marque la commande comme livrée
     * 
     * @return bool
     */
    public function deliver(): bool
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Annule la commande
     * 
     * @return bool
     */
    public function cancel(): bool
    {
        if ($this->status === OrderStatus::DELIVERED) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    // ==========================================
    // ACCESSORS & HELPER METHODS
    // ==========================================

    /**
     * Obtient l'adresse complète formatée
     * 
     * Usage : {{ $order->full_shipping_address }}
     * 
     * @return string
     */
    public function getFullShippingAddressAttribute(): string
    {
        return "{$this->shipping_address}, {$this->shipping_postal_code} {$this->shipping_city}";
    }

    /**
     * Formatte le total pour l'affichage
     * 
     * Usage : {{ $order->formatted_total }}
     * 
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2, ',', ' ') . ' €';
    }

    /**
     * Obtient le badge de statut pour Filament
     * 
     * @return array
     */
    public function getStatusBadgeAttribute(): array
    {
        return [
            'label' => $this->status->label(),
            'color' => $this->status->color(),
            'icon' => $this->status->icon(),
        ];
    }
}