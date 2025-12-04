<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
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
        ];
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un item appartient à un panier
     * Relation Many-to-One
     * 
     * Usage : $cartItem->cart
     * 
     * @return BelongsTo
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Un item fait référence à un produit
     * Relation Many-to-One
     * 
     * Usage : $cartItem->product
     * 
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // ACCESSORS & HELPER METHODS
    // ==========================================

    /**
     * Calcule le sous-total de la ligne (prix × quantité)
     * 
     * Usage : {{ $cartItem->subtotal }}
     * 
     * @return float
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Formatte le prix pour l'affichage
     * 
     * Usage : {{ $cartItem->formatted_price }}
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
     * Usage : {{ $cartItem->formatted_subtotal }}
     * 
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' €';
    }
}