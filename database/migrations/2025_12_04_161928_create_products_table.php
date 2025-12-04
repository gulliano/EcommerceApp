<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des produits
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Relation avec la catégorie
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();  // Si catégorie supprimée, produits aussi
            
            // Informations de base
            $table->string('name');
            $table->string('slug')->unique();
            
            // Description courte pour liste
            $table->string('short_description', 255)->nullable();
            
            // Description complète pour page détail
            $table->text('description')->nullable();
            
            // Prix
            $table->decimal('price', 10, 2);  // 10 chiffres, 2 décimales
            
            // Prix promotionnel (optionnel)
            $table->decimal('sale_price', 10, 2)->nullable();
            
            // Image principale
            $table->string('image')->nullable();
            
            // Images supplémentaires (JSON array de paths)
            $table->json('images')->nullable();
            
            // SKU (Stock Keeping Unit) - référence unique
            $table->string('sku', 50)->unique()->nullable();
            
            // Gestion du stock (même si pas demandé, bon à avoir)
            $table->integer('stock_quantity')->default(0);
            
            // Actif/Inactif
            $table->boolean('is_active')->default(true);
            
            // Produit vedette (pour mise en avant)
            $table->boolean('is_featured')->default(false);
            
            // Ordre d'affichage
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('slug');
            $table->index('category_id');
            $table->index(['is_active', 'is_featured']);
            $table->index('sku');
        });
    }

    /**
     * Supprime la table products
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};