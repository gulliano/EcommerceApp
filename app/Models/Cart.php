<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un panier appartient à un utilisateur
     * Relation Many-to-One
     * 
     * Usage : $cart->user
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un panier contient plusieurs articles
     * Relation One-to-Many
     * 
     * Usage : $cart->items
     * 
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // ==========================================
    // MÉTHODES HELPER
    // ==========================================

    /**
     * Calcule le montant total du panier
     * 
     * Usage : {{ $cart->total }}
     * 
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->subtotal;
        });
    }

    /**
     * Obtient le nombre total d'articles dans le panier
     * 
     * Usage : {{ $cart->total_items }}
     * 
     * @return int
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Vérifie si le panier est vide
     * 
     * Usage : @if($cart->isEmpty()) ... @endif
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Ajoute un produit au panier
     * Si le produit existe déjà, augmente la quantité
     * 
     * Usage : $cart->addItem($product, $quantity)
     * 
     * @param Product $product
     * @param int $quantity
     * @return CartItem
     */
    public function addItem(Product $product, int $quantity = 1): CartItem
    {
        // Vérifie si le produit existe déjà dans le panier
        $cartItem = $this->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            // Augmente la quantité
            $cartItem->increment('quantity', $quantity);
            $cartItem->refresh();
        } else {
            // Crée un nouvel article
            $cartItem = $this->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->effective_price,  // Prix au moment de l'ajout
            ]);
        }

        return $cartItem;
    }

    /**
     * Met à jour la quantité d'un article
     * 
     * Usage : $cart->updateItemQuantity($cartItemId, $newQuantity)
     * 
     * @param int $cartItemId
     * @param int $quantity
     * @return bool
     */
    public function updateItemQuantity(int $cartItemId, int $quantity): bool
    {
        $cartItem = $this->items()->find($cartItemId);

        if (!$cartItem) {
            return false;
        }

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $quantity]);
        }

        return true;
    }

    /**
     * Supprime un article du panier
     * 
     * Usage : $cart->removeItem($cartItemId)
     * 
     * @param int $cartItemId
     * @return bool
     */
    public function removeItem(int $cartItemId): bool
    {
        return $this->items()->where('id', $cartItemId)->delete() > 0;
    }

    /**
     * Vide complètement le panier
     * 
     * Usage : $cart->clear()
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->items()->delete();
    }

    /**
     * Formatte le total pour l'affichage
     * 
     * Usage : {{ $cart->formatted_total }}
     * 
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2, ',', ' ') . ' €';
    }
}