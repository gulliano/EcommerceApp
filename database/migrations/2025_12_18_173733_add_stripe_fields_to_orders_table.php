<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            
            // Identifiant de la session Stripe Checkout
            $table->string('stripe_checkout_session_id')->nullable()->after('total');
            
            // Identifiant du PaymentIntent Stripe
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            
            // Statut du paiement Stripe
            // Valeurs possibles : unpaid, paid, refunded, partially_refunded
            $table->string('payment_status')->default('unpaid')->after('stripe_payment_intent_id');
            
            // Méthode de paiement utilisée
            // Ex: card, paypal, apple_pay, google_pay, etc.
            $table->string('payment_method')->nullable()->after('payment_status');
            
            // Date du paiement
            $table->timestamp('paid_at')->nullable()->after('payment_method');
            
            // Identifiant du remboursement (si applicable)
            $table->string('stripe_refund_id')->nullable()->after('paid_at');
            
            // Montant remboursé
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('stripe_refund_id');
            
            // Date du remboursement
            $table->timestamp('refunded_at')->nullable()->after('refunded_amount');
            
            // Index pour améliorer les performances
            $table->index('stripe_checkout_session_id');
            $table->index('stripe_payment_intent_id');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
             $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropIndex(['payment_status']);
            
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'payment_status',
                'payment_method',
                'paid_at',
                'stripe_refund_id',
                'refunded_amount',
                'refunded_at',
            ]);
        });
    }
};
