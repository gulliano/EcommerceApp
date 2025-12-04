<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\OrderStatus;

return new class extends Migration
{
    /**
     * Crée la table des commandes
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Numéro de commande unique
            // Format : ORD-YYYYMMDD-XXXXX
            $table->string('order_number')->unique();
            
            // Relation avec l'utilisateur (client)
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();  // On ne peut pas supprimer un user avec commandes
            
            // Statut de la commande
            $table->string('status')->default(OrderStatus::PENDING->value);
            
            // Montants
            $table->decimal('subtotal', 10, 2);      // Total des articles
            $table->decimal('tax', 10, 2)->default(0);        // TVA
            $table->decimal('shipping', 10, 2)->default(0);   // Frais de port
            $table->decimal('total', 10, 2);         // Total final
            
            // Informations de livraison (snapshot au moment de la commande)
            $table->string('shipping_name');
            $table->string('shipping_email');
            $table->string('shipping_phone');
            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_postal_code');
            
            // Notes optionnelles
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Dates importantes
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('order_number');
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Supprime la table orders
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};