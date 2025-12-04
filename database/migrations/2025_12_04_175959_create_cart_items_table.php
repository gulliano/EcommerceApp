<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des articles du panier
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            
            // Relation avec le panier
            $table->foreignId('cart_id')
                ->constrained()
                ->cascadeOnDelete();  // Si panier supprimé, items aussi
            
            // Relation avec le produit
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();  // Si produit supprimé, items aussi
            
            // Quantité d'articles
            $table->integer('quantity')->default(1);
            
            // Prix au moment de l'ajout (snapshot)
            // Important : on garde le prix pour éviter les changements de prix
            $table->decimal('price', 10, 2);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['cart_id', 'product_id']);
            
            // Contrainte d'unicité : un produit ne peut être qu'une fois dans un panier
            // Si on veut plus, on augmente la quantité
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Supprime la table cart_items
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};