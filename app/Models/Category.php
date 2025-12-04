<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
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
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ==========================================
    // EVENTS & OBSERVERS
    // ==========================================

    /**
     * Boot du modèle pour auto-générer le slug
     */
    protected static function boot()
    {
        parent::boot();

        // Génère automatiquement le slug avant la création
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Met à jour le slug si le nom change
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Une catégorie peut avoir plusieurs produits
     * Relation One-to-Many
     * 
     * Usage : $category->products
     * 
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Obtient uniquement les produits actifs
     * 
     * Usage : $category->activeProducts
     * 
     * @return HasMany
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    // ==========================================
    // SCOPES (Filtres réutilisables)
    // ==========================================

    /**
     * Scope pour obtenir uniquement les catégories actives
     * 
     * Usage : Category::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour trier par ordre d'affichage
     * 
     * Usage : Category::sorted()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    /**
     * Obtient l'URL complète de l'image
     * 
     * Usage : {{ $category->image_url }}
     * 
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Obtient le nombre de produits dans la catégorie
     * 
     * Usage : {{ $category->products_count }}
     * 
     * @return int
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}