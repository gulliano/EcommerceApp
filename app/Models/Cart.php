<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    /**
     * Relation : un panier appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : un panier a plusieurs items
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Nombre total d'articles dans le panier
     */
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Sous-total (sans taxes ni livraison)
     */
    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * Montant total avec taxes (TVA 8.5% pour la Martinique)
     */
    public function getTaxAttribute()
    {
        return $this->subtotal * 0.085;
    }

    /**
     * Frais de livraison (gratuit si > 50€, sinon 5€)
     */
    public function getShippingAttribute()
    {
        return $this->subtotal >= 50 ? 0 : 5;
    }

    /**
     * Total final (sous-total + taxes + livraison)
     */
    public function getTotalAttribute()
    {
        return $this->subtotal + $this->tax + $this->shipping;
    }

    /**
     * Sous-total formaté
     */
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' €';
    }

    /**
     * Taxes formatées
     */
    public function getFormattedTaxAttribute()
    {
        return number_format($this->tax, 2, ',', ' ') . ' €';
    }

    /**
     * Livraison formatée
     */
    public function getFormattedShippingAttribute()
    {
        return $this->shipping === 0 
            ? 'Gratuit' 
            : number_format($this->shipping, 2, ',', ' ') . ' €';
    }

    /**
     * Total formaté
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2, ',', ' ') . ' €';
    }

    /**
     * Vérifie si le panier est vide
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Vide complètement le panier
     */
    public function clear()
    {
        $this->items()->delete();
    }
}