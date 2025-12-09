<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\Order;
use App\Enums\UserRole;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'postal_code',
    ];

    /**
     * Attributs à cacher lors de la sérialisation
     * (pour les réponses JSON, etc.)
     * 
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting des attributs
     * Le champ 'role' sera automatiquement casté en UserRole enum
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,  // Cast automatique en Enum
        ];
    }

    // ==========================================
    // MÉTHODES HELPER POUR LES RÔLES
    // ==========================================

    /**
     * Vérifie si l'utilisateur est un administrateur
     * 
     * Usage : if ($user->isAdmin()) { ... }
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Vérifie si l'utilisateur est un client
     * 
     * Usage : if ($user->isCustomer()) { ... }
     * 
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    /**
     * Obtient le label du rôle en français
     * 
     * Usage : {{ $user->getRoleLabel() }}
     * 
     * @return string
     */
    public function getRoleLabel(): string
    {
        return $this->role->label();
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un utilisateur peut avoir un panier actif
     * Relation One-to-One
     * 
     * Usage : $user->cart
     * 
     * @return HasOne
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
 * Récupère ou crée le panier de l'utilisateur
 */
public function getOrCreateCart()
{
    if (!$this->cart) {
        $this->cart()->create();
    }
    return $this->cart;
}
    /**
     * Un utilisateur peut avoir plusieurs commandes
     * Relation One-to-Many
     * 
     * Usage : $user->orders
     * 
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtient l'adresse complète formatée
     * Pratique pour affichage
     * 
     * Usage : {{ $user->getFullAddress() }}
     * 
     * @return string|null
     */
    public function getFullAddress(): ?string
    {
        if (!$this->address) {
            return null;
        }

        $parts = array_filter([
            $this->address,
            $this->postal_code,
            $this->city,
        ]);

        return implode(', ', $parts);
    }
}