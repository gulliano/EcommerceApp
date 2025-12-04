<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des paniers
     * Un utilisateur = un panier actif
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // Relation avec l'utilisateur
            // Un user = un seul panier
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();  // Si user supprimé, panier aussi
            
            // Session ID pour paniers non authentifiés (fonctionnalité future)
            $table->string('session_id')->nullable()->unique();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('session_id');
        });
    }

    /**
     * Supprime la table carts
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};