<?php

namespace App\Models;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'sale_price',
        'image',
        'images',
        'sku',
        'stock_quantity',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'images' => 'array',  // JSON vers array PHP
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
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

        // Génère automatiquement le slug avant la création
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            
            // Génère un SKU automatique si vide
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });

        // Met à jour le slug si le nom change
        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un produit appartient à une catégorie
     * Relation Many-to-One
     * 
     * Usage : $product->category
     * 
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Un produit peut être dans plusieurs paniers
     * 
     * Usage : $product->cartItems
     * 
     * @return HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Un produit peut être dans plusieurs commandes
     * 
     * Usage : $product->orderItems
     * 
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==========================================
    // SCOPES (Filtres réutilisables)
    // ==========================================

    /**
     * Scope pour obtenir uniquement les produits actifs
     * 
     * Usage : Product::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour obtenir les produits vedettes
     * 
     * Usage : Product::featured()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope pour filtrer par catégorie
     * 
     * Usage : Product::inCategory($categoryId)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope pour trier par prix
     * 
     * Usage : Product::sortByPrice('asc')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    // ==========================================
    // ACCESSORS & HELPER METHODS
    // ==========================================

    /**
     * Obtient le prix effectif (sale_price si existe, sinon price)
     * 
     * Usage : {{ $product->effective_price }}
     * 
     * @return float
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Vérifie si le produit est en promotion
     * 
     * Usage : @if($product->is_on_sale) ... @endif
     * 
     * @return bool
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Calcule le pourcentage de réduction
     * 
     * Usage : {{ $product->discount_percentage }}%
     * 
     * @return int|null
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->is_on_sale) {
            return null;
        }

        $discount = (($this->price - $this->sale_price) / $this->price) * 100;
        return (int) round($discount);
    }

    /**
     * Obtient l'URL complète de l'image principale
     * 
     * Usage : <img src="{{ $product->image_url }}" />
     * 
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            // Image par défaut si pas d'image
            return asset('images/no-image.png');
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Obtient toutes les URLs des images (principale + additionnelles)
     * 
     * Usage : @foreach($product->all_image_urls as $url) ... @endforeach
     * 
     * @return array
     */
    public function getAllImageUrlsAttribute(): array
    {
        $urls = [$this->image_url];

        if ($this->images) {
            foreach ($this->images as $image) {
                $urls[] = asset('storage/' . $image);
            }
        }

        return $urls;
    }

    /**
     * Vérifie si le produit est en stock
     * 
     * Usage : @if($product->in_stock) ... @endif
     * 
     * @return bool
     */
    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Formatte le prix pour l'affichage
     * 
     * Usage : {{ $product->formatted_price }}
     * 
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Formatte le prix de vente pour l'affichage
     * 
     * Usage : {{ $product->formatted_sale_price }}
     * 
     * @return string|null
     */
    public function getFormattedSalePriceAttribute(): ?string
    {
        if (!$this->sale_price) {
            return null;
        }

        return number_format($this->sale_price, 2, ',', ' ') . ' €';
    }
}