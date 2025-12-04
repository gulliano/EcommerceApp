<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des catégories de produits
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Nom de la catégorie (ex: "Électronique", "Vêtements")
            $table->string('name', 100);
            
            // Slug pour les URLs (ex: "electronique", "vetements")
            // Unique pour éviter les doublons
            $table->string('slug', 100)->unique();
            
            // Description optionnelle de la catégorie
            $table->text('description')->nullable();
            
            // Image de la catégorie (path relatif)
            $table->string('image')->nullable();
            
            // Pour activer/désactiver une catégorie
            // Utile pour cacher temporairement sans supprimer
            $table->boolean('is_active')->default(true);
            
            // Ordre d'affichage (pour tri manuel)
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Index pour améliorer les performances de recherche
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Supprime la table categories
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};