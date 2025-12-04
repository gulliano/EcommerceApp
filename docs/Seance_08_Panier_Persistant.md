 Séance 8 : Gestion du Panier Persistant

**Formation** : CDA - Concepteur Développeur d'Applications  
**Auteur** : Gulliano - IMFPA Martinique  
**Durée** : 3 heures  
**Prérequis** : Séances 1 à 7 complétées

---

## 🎯 Objectifs de la Séance

À la fin de cette séance, vous saurez :
- ✅ Créer les modèles Cart et CartItem avec relations
- ✅ Gérer un panier persistant en base de données
- ✅ Créer un CartController complet
- ✅ Développer les vues du panier
- ✅ Calculer automatiquement les totaux
- ✅ Gérer les quantités et suppressions
- ✅ Vider le panier
- ✅ Créer le processus de commande (checkout)

---

## 📋 Plan de la Séance

1. Création des migrations Cart et CartItem
2. Création des modèles avec relations
3. Création du CartController
4. Création des vues du panier
5. Implémentation du processus de commande
6. Tests et validation

---

## 1️⃣ Création des Migrations

### Migration Cart

```bash
php artisan make:migration create_carts_table
```

Modifiez `database/migrations/xxxx_create_carts_table.php` :

```php
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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
```

**💡 Explication** :
- `user_id` : référence l'utilisateur propriétaire du panier
- `onDelete('cascade')` : supprime le panier si l'utilisateur est supprimé
- Index sur `user_id` pour optimiser les requêtes

---

### Migration CartItem

```bash
php artisan make:migration create_cart_items_table
```

Modifiez `database/migrations/xxxx_create_cart_items_table.php` :

```php
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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2); // Prix au moment de l'ajout
            $table->timestamps();
            
            // Index composé pour optimiser les recherches
            $table->index(['cart_id', 'product_id']);
            
            // Un produit ne peut être qu'une seule fois dans un panier
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
```

**💡 Explication** :
- `cart_id` : référence le panier
- `product_id` : référence le produit
- `quantity` : quantité du produit (défaut 1)
- `price` : prix sauvegardé au moment de l'ajout (pour historique)
- `unique(['cart_id', 'product_id'])` : empêche les doublons

---

### Exécution des migrations

```bash
php artisan migrate
```

---

## 2️⃣ Création des Modèles

### Modèle Cart

```bash
php artisan make:model Cart
```

Modifiez `app/Models/Cart.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    /**
     * Relation : un panier appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : un panier a plusieurs items
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Nombre total d'articles dans le panier
     */
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Sous-total (sans taxes ni livraison)
     */
    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * Montant total avec taxes (TVA 8.5% pour la Martinique)
     */
    public function getTaxAttribute()
    {
        return $this->subtotal * 0.085;
    }

    /**
     * Frais de livraison (gratuit si > 50€, sinon 5€)
     */
    public function getShippingAttribute()
    {
        return $this->subtotal >= 50 ? 0 : 5;
    }

    /**
     * Total final (sous-total + taxes + livraison)
     */
    public function getTotalAttribute()
    {
        return $this->subtotal + $this->tax + $this->shipping;
    }

    /**
     * Sous-total formaté
     */
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' €';
    }

    /**
     * Taxes formatées
     */
    public function getFormattedTaxAttribute()
    {
        return number_format($this->tax, 2, ',', ' ') . ' €';
    }

    /**
     * Livraison formatée
     */
    public function getFormattedShippingAttribute()
    {
        return $this->shipping === 0 
            ? 'Gratuit' 
            : number_format($this->shipping, 2, ',', ' ') . ' €';
    }

    /**
     * Total formaté
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2, ',', ' ') . ' €';
    }

    /**
     * Vérifie si le panier est vide
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Vide complètement le panier
     */
    public function clear()
    {
        $this->items()->delete();
    }
}
```

**💡 Points clés** :
- **Attributs calculés** : `total_items`, `subtotal`, `tax`, `shipping`, `total`
- **Formatage** : méthodes `formatted_*` pour affichage
- **Méthodes utilitaires** : `isEmpty()`, `clear()`

---

### Modèle CartItem

```bash
php artisan make:model CartItem
```

Modifiez `app/Models/CartItem.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * Relation : un item appartient à un panier
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Relation : un item référence un produit
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Sous-total de cet item (quantité × prix)
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    /**
     * Sous-total formaté
     */
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' €';
    }

    /**
     * Prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Incrémente la quantité
     */
    public function increment()
    {
        $this->quantity++;
        $this->save();
    }

    /**
     * Décrémente la quantité (minimum 1)
     */
    public function decrement()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
            $this->save();
        }
    }
}
```

**💡 Points clés** :
- Stocke le **prix au moment de l'ajout** (important pour l'historique)
- Méthodes `increment()` et `decrement()` pour gérer les quantités
- Calcul du sous-total par item

---

### Modification du modèle User

Ajoutez la relation dans `app/Models/User.php` :

```php
/**
 * Relation : un utilisateur a un panier
 */
public function cart()
{
    return $this->hasOne(Cart::class);
}

/**
 * Récupère ou crée le panier de l'utilisateur
 */
public function getOrCreateCart()
{
    if (!$this->cart) {
        $this->cart()->create();
    }
    return $this->cart;
}
```

---

## 3️⃣ Création du CartController

```bash
php artisan make:controller CartController
```

Modifiez `app/Http/Controllers/CartController.php` :

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Affiche le panier
     */
    public function index()
    {
        $cart = auth()->user()->getOrCreateCart();
        $cart->load(['items.product.category']);

        return view('cart.index', compact('cart'));
    }

    /**
     * Ajoute un produit au panier
     */
    public function add(Product $product)
    {
        // Vérifie que le produit est disponible
        if (!$product->in_stock) {
            return back()->with('error', 'Ce produit n\'est plus en stock.');
        }

        $cart = auth()->user()->getOrCreateCart();

        // Vérifie si le produit est déjà dans le panier
        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            // Incrémente la quantité si déjà présent
            $cartItem->increment();
            $message = 'Quantité mise à jour dans votre panier.';
        } else {
            // Ajoute un nouvel item
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->sale_price ?? $product->price,
            ]);
            $message = 'Produit ajouté à votre panier.';
        }

        return back()->with('success', $message);
    }

    /**
     * Met à jour la quantité d'un item
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Vérifie que l'item appartient bien au panier de l'utilisateur
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        // Vérifie le stock disponible
        if ($request->quantity > $cartItem->product->stock_quantity) {
            return back()->with('error', 'Stock insuffisant pour cette quantité.');
        }

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        return back()->with('success', 'Quantité mise à jour.');
    }

    /**
     * Retire un item du panier
     */
    public function remove(CartItem $cartItem)
    {
        // Vérifie que l'item appartient bien au panier de l'utilisateur
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403);
        }

        $cartItem->delete();

        return back()->with('success', 'Produit retiré du panier.');
    }

    /**
     * Vide complètement le panier
     */
    public function clear()
    {
        $cart = auth()->user()->cart;

        if ($cart) {
            $cart->clear();
        }

        return back()->with('success', 'Panier vidé.');
    }
}
```

**💡 Sécurité et validation** :
- Vérifie que l'utilisateur authentifié possède bien les items
- Valide les quantités (min 1, max 99)
- Vérifie le stock disponible avant mise à jour
- Messages flash pour retour utilisateur

---

## 4️⃣ Création des Vues du Panier

### Vue principale du panier

Créez `resources/views/cart/index.blade.php` :

```blade
@extends('layouts.app')

@section('title', 'Mon Panier')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <h1 class="text-3xl font-bold mb-8">🛒 Mon Panier</h1>

    @if($cart->isEmpty())
        <!-- Panier vide -->
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="text-gray-500 text-lg mb-6">Votre panier est vide</p>
            <a href="{{ route('products.index') }}" 
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                Continuer mes achats
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Liste des articles -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cart->items as $item)
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex gap-6">
                            
                            <!-- Image du produit -->
                            <div class="flex-shrink-0">
                                <img src="{{ $item->product->image_url }}" 
                                     alt="{{ $item->product->name }}"
                                     class="w-24 h-24 object-cover rounded-lg">
                            </div>

                            <!-- Détails du produit -->
                            <div class="flex-grow">
                                <!-- Nom et catégorie -->
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <a href="{{ route('products.show', $item->product->slug) }}" 
                                           class="text-lg font-semibold hover:text-blue-600 transition">
                                            {{ $item->product->name }}
                                        </a>
                                        <p class="text-sm text-gray-500">
                                            {{ $item->product->category->name }}
                                        </p>
                                    </div>
                                    
                                    <!-- Bouton supprimer -->
                                    <form action="{{ route('cart.remove', $item) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 transition"
                                                onclick="return confirm('Supprimer cet article ?')">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <!-- Prix et quantité -->
                                <div class="flex justify-between items-center mt-4">
                                    <!-- Prix unitaire -->
                                    <div>
                                        <span class="text-lg font-bold text-gray-900">
                                            {{ $item->formatted_price }}
                                        </span>
                                        @if($item->product->is_on_sale)
                                            <span class="text-sm text-red-600 ml-2">
                                                -{{ $item->product->discount_percentage }}%
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Contrôles de quantité -->
                                    <div class="flex items-center gap-3">
                                        <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            
                                            <label class="text-sm font-semibold text-gray-700">Quantité :</label>
                                            
                                            <select name="quantity" 
                                                    onchange="this.form.submit()"
                                                    class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                                                @for($i = 1; $i <= min(99, $item->product->stock_quantity); $i++)
                                                    <option value="{{ $i }}" {{ $item->quantity == $i ? 'selected' : '' }}>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </form>

                                        <!-- Sous-total de l'item -->
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">Sous-total</p>
                                            <p class="text-lg font-bold text-gray-900">
                                                {{ $item->formatted_subtotal }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock disponible -->
                                @if($item->product->stock_quantity < 10)
                                    <p class="text-sm text-orange-600 mt-2">
                                        ⚠️ Plus que {{ $item->product->stock_quantity }} en stock !
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Bouton vider le panier -->
                <div class="flex justify-between items-center pt-4">
                    <a href="{{ route('products.index') }}" 
                       class="text-blue-600 hover:text-blue-800 font-semibold">
                        ← Continuer mes achats
                    </a>

                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-800 font-semibold"
                                onclick="return confirm('Vider complètement le panier ?')">
                            Vider le panier
                        </button>
                    </form>
                </div>
            </div>

            <!-- Récapitulatif de la commande -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-6">Récapitulatif</h2>

                    <!-- Lignes de détail -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-700">
                            <span>Sous-total ({{ $cart->total_items }} articles)</span>
                            <span class="font-semibold">{{ $cart->formatted_subtotal }}</span>
                        </div>

                        <div class="flex justify-between text-gray-700">
                            <span>TVA (8.5%)</span>
                            <span class="font-semibold">{{ $cart->formatted_tax }}</span>
                        </div>

                        <div class="flex justify-between text-gray-700">
                            <span>Livraison</span>
                            <span class="font-semibold">{{ $cart->formatted_shipping }}</span>
                        </div>

                        @if($cart->shipping === 0)
                            <p class="text-sm text-green-600">
                                ✅ Livraison gratuite !
                            </p>
                        @else
                            <p class="text-sm text-gray-500">
                                💡 Plus que {{ number_format(50 - $cart->subtotal, 2) }} € pour la livraison gratuite
                            </p>
                        @endif
                    </div>

                    <!-- Total -->
                    <div class="border-t pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold">Total</span>
                            <span class="text-2xl font-bold text-blue-600">{{ $cart->formatted_total }}</span>
                        </div>
                    </div>

                    <!-- Bouton commander -->
                    <a href="{{ route('checkout.index') }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center transition shadow-lg hover:shadow-xl">
                        Passer la commande
                    </a>

                    <!-- Paiements acceptés -->
                    <div class="mt-6 pt-6 border-t">
                        <p class="text-xs text-gray-500 text-center mb-2">Paiements sécurisés</p>
                        <div class="flex justify-center gap-2">
                            <span class="text-2xl">💳</span>
                            <span class="text-2xl">🏦</span>
                            <span class="text-2xl">📱</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
```

**💡 Fonctionnalités de la vue** :
- **État vide** : message si panier vide
- **Liste items** : image, nom, prix, quantité, sous-total
- **Contrôles** : sélecteur quantité, bouton supprimer
- **Récapitulatif** : sous-total, TVA, livraison, total
- **Alertes stock** : avertissement si stock < 10

---

## 5️⃣ Processus de Commande (Checkout)

### Routes du checkout

Ajoutez dans `routes/web.php` :

```php
// Processus de commande (authentification requise)
Route::middleware(['auth'])->group(function () {
    // ... routes panier existantes ...
    
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
});
```

---

### Création du CheckoutController

```bash
php artisan make:controller CheckoutController
```

Modifiez `app/Http/Controllers/CheckoutController.php` :

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Affiche la page de validation de commande
     */
    public function index()
    {
        $cart = auth()->user()->cart;

        // Redirige si panier vide
        if (!$cart || $cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Votre panier est vide.');
        }

        $cart->load(['items.product.category']);

        return view('checkout.index', compact('cart'));
    }

    /**
     * Traite la commande
     */
    public function process(Request $request)
    {
        $cart = auth()->user()->cart;

        // Vérifie que le panier n'est pas vide
        if (!$cart || $cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Votre panier est vide.');
        }

        // Validation des données de livraison
        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_city' => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Vérifie le stock de tous les produits
            foreach ($cart->items as $item) {
                if ($item->product->stock_quantity < $item->quantity) {
                    throw new \Exception("Stock insuffisant pour {$item->product->name}");
                }
            }

            // Crée la commande
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'CMD-' . strtoupper(uniqid()),
                'status' => 'PENDING',
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping' => $cart->shipping,
                'total' => $cart->total,
                'shipping_name' => $validated['shipping_name'],
                'shipping_email' => $validated['shipping_email'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'shipping_city' => $validated['shipping_city'],
            ]);

            // Crée les items de commande et décrémente le stock
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                // Décrémente le stock
                $item->product->decrement('stock_quantity', $item->quantity);
            }

            // Vide le panier
            $cart->clear();

            DB::commit();

            return redirect()->route('checkout.success', $order)
                ->with('success', 'Commande passée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la commande : ' . $e->getMessage());
        }
    }

    /**
     * Page de confirmation de commande
     */
    public function success(Order $order)
    {
        // Vérifie que la commande appartient à l'utilisateur connecté
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'user']);

        return view('checkout.success', compact('order'));
    }
}
```

**💡 Points importants** :
- **Transaction** : utilise `DB::beginTransaction()` pour garantir la cohérence
- **Vérification stock** : vérifie avant de créer la commande
- **Décrémentation** : réduit le stock après validation
- **Sécurité** : vérifie que l'utilisateur possède la commande

---

### Vue page checkout

Créez `resources/views/checkout/index.blade.php` :

```blade
@extends('layouts.app')

@section('title', 'Validation de la commande')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <h1 class="text-3xl font-bold mb-8">✅ Validation de la commande</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Formulaire de livraison -->
        <div class="lg:col-span-2">
            <form action="{{ route('checkout.process') }}" method="POST">
                @csrf

                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold mb-6">📦 Informations de livraison</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nom complet -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Nom complet *</label>
                            <input type="text" 
                                   name="shipping_name" 
                                   value="{{ old('shipping_name', auth()->user()->name) }}"
                                   required
                                   class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            @error('shipping_name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Email *</label>
                            <input type="email" 
                                   name="shipping_email" 
                                   value="{{ old('shipping_email', auth()->user()->email) }}"
                                   required
                                   class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            @error('shipping_email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Téléphone *</label>
                            <input type="tel" 
                                   name="shipping_phone" 
                                   value="{{ old('shipping_phone', auth()->user()->phone) }}"
                                   required
                                   class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            @error('shipping_phone')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Adresse -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Adresse *</label>
                            <textarea name="shipping_address" 
                                      rows="3"
                                      required
                                      class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('shipping_address', auth()->user()->address) }}</textarea>
                            @error('shipping_address')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Code postal -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Code postal *</label>
                            <input type="text" 
                                   name="shipping_postal_code" 
                                   value="{{ old('shipping_postal_code', auth()->user()->postal_code) }}"
                                   required
                                   class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            @error('shipping_postal_code')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ville -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Ville *</label>
                            <input type="text" 
                                   name="shipping_city" 
                                   value="{{ old('shipping_city', auth()->user()->city) }}"
                                   required
                                   class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            @error('shipping_city')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Résumé de la commande (mobile) -->
                <div class="bg-white rounded-lg shadow p-6 mb-6 lg:hidden">
                    <h2 class="text-xl font-bold mb-4">📋 Votre commande</h2>
                    @foreach($cart->items as $item)
                        <div class="flex justify-between text-sm mb-2">
                            <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                            <span class="font-semibold">{{ $item->formatted_subtotal }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Bouton de validation -->
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition shadow-lg hover:shadow-xl">
                    Confirmer la commande
                </button>
            </form>
        </div>

        <!-- Récapitulatif (desktop) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h2 class="text-xl font-bold mb-6">📋 Votre commande</h2>

                <!-- Liste des produits -->
                <div class="space-y-4 mb-6">
                    @foreach($cart->items as $item)
                        <div class="flex gap-3">
                            <img src="{{ $item->product->image_url }}" 
                                 alt="{{ $item->product->name }}"
                                 class="w-16 h-16 object-cover rounded">
                            <div class="flex-grow">
                                <p class="font-semibold text-sm">{{ $item->product->name }}</p>
                                <p class="text-sm text-gray-500">Qté : {{ $item->quantity }}</p>
                                <p class="text-sm font-bold">{{ $item->formatted_subtotal }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Totaux -->
                <div class="border-t pt-4 space-y-2 mb-6">
                    <div class="flex justify-between text-gray-700">
                        <span>Sous-total</span>
                        <span class="font-semibold">{{ $cart->formatted_subtotal }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>TVA (8.5%)</span>
                        <span class="font-semibold">{{ $cart->formatted_tax }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Livraison</span>
                        <span class="font-semibold">{{ $cart->formatted_shipping }}</span>
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold">Total</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $cart->formatted_total }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
```

---

### Vue page succès

Créez `resources/views/checkout/success.blade.php` :

```blade
@extends('layouts.app')

@section('title', 'Commande confirmée')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Message de succès -->
    <div class="bg-green-50 border-2 border-green-500 rounded-lg p-8 mb-8 text-center">
        <svg class="mx-auto h-16 w-16 text-green-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h1 class="text-3xl font-bold text-green-800 mb-2">🎉 Commande confirmée !</h1>
        <p class="text-green-700 text-lg">
            Merci pour votre commande. Un email de confirmation vous a été envoyé.
        </p>
    </div>

    <!-- Détails de la commande -->
    <div class="bg-white rounded-lg shadow p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6">Détails de votre commande</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Numéro de commande -->
            <div>
                <p class="text-sm text-gray-500">Numéro de commande</p>
                <p class="font-mono text-lg font-bold">{{ $order->order_number }}</p>
            </div>

            <!-- Date -->
            <div>
                <p class="text-sm text-gray-500">Date</p>
                <p class="font-semibold">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>

            <!-- Statut -->
            <div>
                <p class="text-sm text-gray-500">Statut</p>
                <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                    En attente de traitement
                </span>
            </div>

            <!-- Total -->
            <div>
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-2xl font-bold text-blue-600">{{ $order->formatted_total }}</p>
            </div>
        </div>

        <!-- Adresse de livraison -->
        <div class="border-t pt-6 mb-8">
            <h3 class="font-bold text-lg mb-3">📦 Adresse de livraison</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="font-semibold">{{ $order->shipping_name }}</p>
                <p class="text-gray-700">{{ $order->shipping_address }}</p>
                <p class="text-gray-700">{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                <p class="text-gray-700 mt-2">📧 {{ $order->shipping_email }}</p>
                <p class="text-gray-700">📞 {{ $order->shipping_phone }}</p>
            </div>
        </div>

        <!-- Articles commandés -->
        <div class="border-t pt-6">
            <h3 class="font-bold text-lg mb-4">Articles commandés</h3>
            <div class="space-y-4">
                @foreach($order->items as $item)
                    <div class="flex gap-4">
                        <img src="{{ $item->product->image_url }}" 
                             alt="{{ $item->product_name }}"
                             class="w-20 h-20 object-cover rounded">
                        <div class="flex-grow">
                            <p class="font-semibold">{{ $item->product_name }}</p>
                            <p class="text-sm text-gray-500">Quantité : {{ $item->quantity }}</p>
                            <p class="text-sm">Prix unitaire : {{ $item->formatted_price }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">{{ $item->formatted_subtotal }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('products.index') }}" 
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg text-center transition">
            Continuer mes achats
        </a>
        <a href="{{ url('/customer') }}" 
           class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-lg text-center transition">
            Voir mes commandes
        </a>
    </div>

</div>
@endsection
```

---

## 6️⃣ Tests et Validation

### Tests à effectuer

1. **Ajout au panier** :
   ```bash
   php artisan serve
   ```
   - Naviguer vers un produit
   - Cliquer sur "Ajouter au panier"
   - Vérifier le message de succès
   - Vérifier le badge dans la navigation

2. **Page panier** :
   - Accéder à `/cart`
   - Vérifier l'affichage de tous les items
   - Modifier une quantité
   - Supprimer un item
   - Vider le panier

3. **Processus de commande** :
   - Cliquer sur "Passer la commande"
   - Remplir le formulaire de livraison
   - Valider la commande
   - Vérifier la page de succès
   - Vérifier en base de données

4. **Vérifications BDD** :
   ```bash
   php artisan tinker
   ```
   ```php
   // Vérifier le panier
   $cart = \App\Models\Cart::with('items.product')->first();
   $cart->total_items;
   $cart->formatted_total;
   
   // Vérifier les commandes
   $order = \App\Models\Order::with('items')->latest()->first();
   $order->order_number;
   $order->formatted_total;
   ```

---

## ✅ Checklist de Validation

- [ ] Migrations créées et exécutées
- [ ] Modèles Cart et CartItem fonctionnels
- [ ] Relations correctement définies
- [ ] CartController complet
- [ ] CheckoutController opérationnel
- [ ] Vues du panier stylisées
- [ ] Vue checkout complète
- [ ] Vue succès affichée
- [ ] Calculs des totaux corrects
- [ ] Stock décrémenté après commande
- [ ] Panier vidé après commande
- [ ] Messages flash fonctionnels
- [ ] Sécurité : vérification propriétaire

---

## 🎯 Points de Validation - Séance 8

- ✅ Le panier persiste en base de données
- ✅ Les quantités sont modifiables
- ✅ Les items peuvent être supprimés
- ✅ Le panier peut être vidé
- ✅ Les totaux sont calculés correctement (sous-total, TVA, livraison, total)
- ✅ La livraison est gratuite au-dessus de 50€
- ✅ Le processus de commande fonctionne
- ✅ Le stock est décrémenté après commande
- ✅ Le panier est vidé après commande
- ✅ La page de confirmation s'affiche

---

## 💾 Commit Git

```bash
git add .
git commit -m "Séance 8: Panier persistant avec Cart, CartItem, CartController, CheckoutController et processus de commande complet"
git push
```

---

## 📝 Récapitulatif de la Séance

### Fichiers créés/modifiés

**Migrations** :
- `database/migrations/xxxx_create_carts_table.php`
- `database/migrations/xxxx_create_cart_items_table.php`

**Modèles** :
- `app/Models/Cart.php`
- `app/Models/CartItem.php`
- `app/Models/User.php` (ajout relation cart)

**Controllers** :
- `app/Http/Controllers/CartController.php`
- `app/Http/Controllers/CheckoutController.php`

**Routes** :
- `routes/web.php` (ajout routes panier et checkout)

**Vues** :
- `resources/views/cart/index.blade.php`
- `resources/views/checkout/index.blade.php`
- `resources/views/checkout/success.blade.php`

### Concepts abordés

1. **Panier persistant** : stockage en BDD au lieu de session
2. **Relations Eloquent** : hasOne, hasMany, belongsTo
3. **Accesseurs** : `getTotalAttribute()`, `getFormattedTotalAttribute()`
4. **Transactions** : `DB::beginTransaction()`, `commit()`, `rollBack()`
5. **Validation** : règles de validation Laravel
6. **Sécurité** : vérification propriétaire, vérification stock
7. **Messages flash** : `with('success')`, `with('error')`
8. **Eager loading** : `with(['items.product.category'])`

---

## 🚀 Prochaine Séance

**Séance 9 : Panel Customer - Panier & Commandes**
- Resource Order dans panel customer
- Affichage historique des commandes
- Détail d'une commande
- Filtre par statut
- Dashboard client personnalisé

---

**🎉 Félicitations ! La Séance 8 est terminée !**

Vous avez maintenant un **système de panier e-commerce complet** avec :
- Persistance en base de données
- Gestion des quantités
- Calcul automatique des totaux
- Processus de commande sécurisé
- Décrémentation du stock
- Page de confirmation

**Prêt pour la Séance 9 ?** 🚀