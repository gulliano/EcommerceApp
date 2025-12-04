<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des articles commandés
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // Relation avec la commande
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();  // Si commande supprimée, items aussi
            
            // Relation avec le produit
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();  // On ne peut pas supprimer un produit commandé
            
            // Snapshot des informations au moment de la commande
            // Important : on garde ces infos même si le produit change après
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            
            // Quantité commandée
            $table->integer('quantity');
            
            // Prix unitaire au moment de la commande
            $table->decimal('price', 10, 2);
            
            // Sous-total de la ligne (price × quantity)
            // Stocké pour éviter les recalculs
            $table->decimal('subtotal', 10, 2);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Supprime la table order_items
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};