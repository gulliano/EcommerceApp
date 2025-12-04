<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'price',
        'subtotal',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    // ==========================================
    // EVENTS & OBSERVERS
    // ==========================================

    /**
     * Boot du modèle pour calculs automatiques
     */
    protected static function boot()
    {
        parent::boot();

        // Calcule automatiquement le subtotal avant la sauvegarde
        static::saving(function ($orderItem) {
            $orderItem->subtotal = $orderItem->price * $orderItem->quantity;
        });
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un item appartient à une commande
     * Relation Many-to-One
     * 
     * Usage : $orderItem->order
     * 
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Un item fait référence à un produit
     * Relation Many-to-One
     * 
     * Usage : $orderItem->product
     * 
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Formatte le prix pour l'affichage
     * 
     * Usage : {{ $orderItem->formatted_price }}
     * 
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Formatte le sous-total pour l'affichage
     * 
     * Usage : {{ $orderItem->formatted_subtotal }}
     * 
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' €';
    }
}