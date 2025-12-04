<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserRole;

return new class extends Migration
{
    /**
     * Ajoute les champs nécessaires pour la gestion des rôles
     * et les informations client
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajout du champ role avec valeur par défaut 'customer'
            $table->string('role')
                ->default(UserRole::CUSTOMER->value)
                ->after('email');
            
            // Champs additionnels pour les clients
            // Ces champs sont utiles pour les adresses de livraison
            $table->string('phone', 20)->nullable()->after('role');
            $table->text('address')->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('postal_code', 10)->nullable()->after('city');
        });
    }

    /**
     * Supprime les colonnes ajoutées lors du rollback
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 
                'phone', 
                'address', 
                'city', 
                'postal_code'
            ]);
        });
    }
};