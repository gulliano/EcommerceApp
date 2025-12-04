# Séance 7 : Frontend Public - Catalogue Produits

**Formation** : CDA - Concepteur Développeur d'Applications  
**Auteur** : Gulliano - IMFPA Martinique  
**Durée** : 3 heures  
**Prérequis** : Documents 1 et 2 complétés

---

## 🎯 Objectifs de la Séance

À la fin de cette séance, vous saurez :
- ✅ Créer des routes publiques pour le catalogue
- ✅ Développer des controllers pour les produits
- ✅ Créer des vues Blade avec Tailwind CSS
- ✅ Implémenter la pagination et les filtres
- ✅ Créer une page détail produit
- ✅ Gérer l'affichage des promotions

---

## 📋 Plan de la Séance

1. Configuration des routes publiques
2. Création des Controllers (Home, Product)
3. Modification du layout et de la navigation
4. Création de la page d'accueil
5. Création du composant Product Card
6. Création de la page liste produits avec filtres
7. Création de la page détail produit
8. Création de la page catégorie
9. Tests et validation

---

## 1️⃣ Configuration des Routes Publiques

### Modification de `routes/web.php`

Ouvrez le fichier `routes/web.php` et **ajoutez** ces routes AVANT la ligne `require __DIR__.'/auth.php';` :

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Catalogue produits
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Catégories
Route::get('/categories/{category:slug}', [ProductController::class, 'category'])->name('categories.show');

// Panier (authentification requise)
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

require __DIR__.'/auth.php';
```

**💡 Explication** :
- Les routes produits utilisent le **route model binding** avec le slug
- Les routes panier sont protégées par le middleware `auth`
- Le groupe `middleware` permet de protéger plusieurs routes en une fois

---

## 2️⃣ Création du HomeController

### Commande de création

```bash
php artisan make:controller HomeController
```

### Code du `app/Http/Controllers/HomeController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil avec produits vedettes et catégories
     */
    public function index()
    {
        // Récupère les catégories actives avec leurs compteurs de produits
        $categories = Category::active()
            ->sorted()
            ->withCount('activeProducts')
            ->get();

        // Récupère les produits vedettes (max 8)
        $featuredProducts = Product::active()
            ->featured()
            ->with('category')
            ->take(8)
            ->get();

        // Récupère les nouveaux produits (max 8)
        $newProducts = Product::active()
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        // Récupère les produits en promotion (max 8)
        $saleProducts = Product::active()
            ->whereNotNull('sale_price')
            ->with('category')
            ->take(8)
            ->get();

        return view('home', compact(
            'categories',
            'featuredProducts',
            'newProducts',
            'saleProducts'
        ));
    }
}
```

**💡 Points importants** :
- Utilise les **query scopes** définis dans les modèles (`active()`, `featured()`, `sorted()`)
- `withCount('activeProducts')` compte les produits actifs de chaque catégorie
- `with('category')` fait un **eager loading** pour éviter les problèmes N+1
- `compact()` crée un tableau associatif pour passer les données à la vue

---

## 3️⃣ Création du ProductController

### Commande de création

```bash
php artisan make:controller ProductController
```

### Code du `app/Http/Controllers/ProductController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Affiche la liste de tous les produits avec filtres
     */
    public function index(Request $request)
    {
        // Construction de la requête de base
        $query = Product::query()
            ->active()
            ->with('category');

        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filtre par prix minimum
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Filtre par prix maximum
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filtre par disponibilité en stock
        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Filtre promotions uniquement
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('sale_price');
        }

        // Tri des résultats
        $sortBy = $request->get('sort', 'newest');
        match($sortBy) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(),
        };

        // Pagination (12 produits par page)
        // withQueryString() conserve les paramètres de filtre dans la pagination
        $products = $query->paginate(12)->withQueryString();

        // Récupère toutes les catégories pour le filtre
        $categories = Category::active()
            ->sorted()
            ->withCount('activeProducts')
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Affiche le détail d'un produit
     */
    public function show(Product $product)
    {
        // Vérifie que le produit est actif
        if (!$product->is_active) {
            abort(404);
        }

        // Charge les relations
        $product->load('category');

        // Produits similaires (même catégorie, max 4)
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Affiche les produits d'une catégorie
     */
    public function category(Category $category)
    {
        // Vérifie que la catégorie est active
        if (!$category->is_active) {
            abort(404);
        }

        // Récupère les produits de la catégorie
        $products = Product::active()
            ->where('category_id', $category->id)
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('categories.show', compact('category', 'products'));
    }
}
```

**💡 Explication des méthodes** :

**`index()`** : Liste tous les produits avec filtres avancés
- Filtre par catégorie, prix, stock, promotions
- Tri personnalisable (prix, nom, date)
- Pagination avec conservation des filtres

**`show()`** : Affiche un produit en détail
- Vérifie que le produit est actif (sinon 404)
- Charge les produits similaires de la même catégorie

**`category()`** : Affiche les produits d'une catégorie
- Vérifie que la catégorie est active
- Pagination des produits de cette catégorie

---

## 4️⃣ Modification de la Navigation

### Modification de `resources/views/layouts/navigation.blade.php`

**Remplacez** la section des liens de navigation par ce code :

```blade
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
        🏠 Accueil
    </x-nav-link>

    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
        🛍️ Produits
    </x-nav-link>

    @auth
        <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">
            🛒 Mon Panier
            @if(auth()->user()->cart && auth()->user()->cart->total_items > 0)
                <span class="ml-1 bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                    {{ auth()->user()->cart->total_items }}
                </span>
            @endif
        </x-nav-link>
    @endauth
</div>
```

**💡 Explication** :
- Liens vers Accueil, Produits, Panier
- Badge sur le panier affichant le nombre d'articles (si connecté)
- `request()->routeIs('products.*')` active le lien si on est sur une route produit

---

## 5️⃣ Création de la Page d'Accueil

### Création de `resources/views/home.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 mb-12 text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">
            Bienvenue sur notre Boutique
        </h1>
        <p class="text-xl mb-6">
            Découvrez nos produits de qualité à prix compétitifs
        </p>
        <a href="{{ route('products.index') }}" 
           class="inline-block bg-white text-blue-700 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition">
            Voir tous les produits →
        </a>
    </div>

    <!-- Catégories -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Nos Catégories</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" 
                   class="bg-white rounded-lg shadow hover:shadow-lg transition p-4 text-center group">
                    @if($category->image)
                        <img src="{{ $category->image_url }}" 
                             alt="{{ $category->name }}" 
                             class="w-16 h-16 object-cover rounded-full mx-auto mb-2 group-hover:scale-110 transition">
                    @else
                        <div class="w-16 h-16 bg-gray-200 rounded-full mx-auto mb-2 flex items-center justify-center group-hover:scale-110 transition">
                            <span class="text-2xl">📦</span>
                        </div>
                    @endif
                    <h3 class="font-semibold text-sm">{{ $category->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $category->active_products_count }} produits</p>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Produits Vedettes -->
    @if($featuredProducts->isNotEmpty())
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">⭐ Produits Vedettes</h2>
                <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Voir tout →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    @include('products.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Promotions -->
    @if($saleProducts->isNotEmpty())
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">🔥 Promotions</h2>
                <a href="{{ route('products.index', ['on_sale' => 1]) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Voir tout →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($saleProducts as $product)
                    @include('products.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Nouveautés -->
    @if($newProducts->isNotEmpty())
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">🆕 Nouveautés</h2>
                <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Voir tout →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($newProducts as $product)
                    @include('products.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
```

**💡 Structure de la page** :
1. **Hero Section** : Bannière d'accueil avec call-to-action
2. **Catégories** : Grille de toutes les catégories avec compteur
3. **Sections dynamiques** : Vedettes, Promotions, Nouveautés (affichées uniquement si produits disponibles)

---

## 6️⃣ Création du Composant Product Card

### Création de `resources/views/products/partials/product-card.blade.php`

Créez d'abord les dossiers :
```bash
mkdir -p resources/views/products/partials
```

Puis créez le fichier :

```blade
<div class="bg-white rounded-lg shadow hover:shadow-xl transition duration-300 overflow-hidden">
    <!-- Image -->
    <div class="relative">
        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ $product->image_url }}" 
                 alt="{{ $product->name }}"
                 class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
        </a>

        <!-- Badge promo -->
        @if($product->is_on_sale)
            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow-lg">
                -{{ $product->discount_percentage }}%
            </div>
        @endif

        <!-- Badge vedette -->
        @if($product->is_featured)
            <div class="absolute top-2 left-2 bg-yellow-400 text-gray-800 text-xs font-bold px-2 py-1 rounded shadow-lg">
                ⭐ Vedette
            </div>
        @endif

        <!-- Badge rupture de stock -->
        @if(!$product->in_stock)
            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                <span class="bg-red-600 text-white px-4 py-2 rounded font-bold shadow-lg">
                    Rupture de stock
                </span>
            </div>
        @endif
    </div>

    <!-- Contenu -->
    <div class="p-4">
        <!-- Catégorie -->
        <a href="{{ route('categories.show', $product->category->slug) }}" 
           class="text-xs text-gray-500 hover:text-blue-600 transition">
            {{ $product->category->name }}
        </a>

        <!-- Nom -->
        <a href="{{ route('products.show', $product->slug) }}">
            <h3 class="font-semibold text-lg mt-1 hover:text-blue-600 transition line-clamp-2 h-14">
                {{ $product->name }}
            </h3>
        </a>

        <!-- Description courte -->
        @if($product->short_description)
            <p class="text-sm text-gray-600 mt-2 line-clamp-2 h-10">
                {{ $product->short_description }}
            </p>
        @endif

        <!-- Prix -->
        <div class="mt-4 flex items-center justify-between">
            <div>
                @if($product->is_on_sale)
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold text-red-600">
                            {{ $product->formatted_sale_price }}
                        </span>
                        <span class="text-sm text-gray-500 line-through">
                            {{ $product->formatted_price }}
                        </span>
                    </div>
                @else
                    <span class="text-xl font-bold text-gray-900">
                        {{ $product->formatted_price }}
                    </span>
                @endif
            </div>

            <!-- Bouton Ajouter au panier -->
            @auth
                @if($product->in_stock)
                    <form action="{{ route('cart.add', $product) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg transition shadow hover:shadow-lg"
                                title="Ajouter au panier">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 rounded-lg text-xs transition">
                    Connexion
                </a>
            @endauth
        </div>
    </div>
</div>
```

**💡 Fonctionnalités de la card** :
- **Image** avec hover effect (zoom)
- **Badges** : promotion (%), vedette (⭐), rupture de stock
- **Prix** : affiche le prix normal ou le prix promo barré
- **Bouton panier** : visible uniquement si connecté ET en stock
- **Responsive** : s'adapte à toutes les tailles d'écran

---

## 7️⃣ Création de la Page Liste Produits

### Création de `resources/views/products/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Tous les produits')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Tous les produits</h1>
        <p class="text-gray-600">{{ $products->total() }} produits trouvés</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Sidebar Filtres -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h2 class="font-bold text-lg mb-4">🔍 Filtres</h2>

                <form method="GET" action="{{ route('products.index') }}">
                    
                    <!-- Catégorie -->
                    <div class="mb-6">
                        <label class="block font-semibold mb-2">Catégorie</label>
                        <select name="category" class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->active_products_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Prix -->
                    <div class="mb-6">
                        <label class="block font-semibold mb-2">Prix (€)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" 
                                   name="min_price" 
                                   placeholder="Min" 
                                   value="{{ request('min_price') }}"
                                   step="0.01"
                                   class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <input type="number" 
                                   name="max_price" 
                                   placeholder="Max"
                                   value="{{ request('max_price') }}"
                                   step="0.01"
                                   class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="mb-6 space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="in_stock" 
                                   value="1" 
                                   {{ request('in_stock') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm">En stock uniquement</span>
                        </label>

                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="on_sale" 
                                   value="1"
                                   {{ request('on_sale') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm">En promotion</span>
                        </label>
                    </div>

                    <!-- Tri -->
                    <div class="mb-6">
                        <label class="block font-semibold mb-2">Trier par</label>
                        <select name="sort" class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>
                                Plus récents
                            </option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                Prix croissant
                            </option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                Prix décroissant
                            </option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                                Nom (A-Z)
                            </option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                                Nom (Z-A)
                            </option>
                        </select>
                    </div>

                    <!-- Boutons -->
                    <div class="space-y-2">
                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition font-semibold shadow hover:shadow-lg">
                            Appliquer les filtres
                        </button>

                        <a href="{{ route('products.index') }}" 
                           class="block w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-lg transition font-semibold">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grille de produits -->
        <div class="lg:col-span-3">
            @if($products->isEmpty())
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 text-lg mb-4">Aucun produit trouvé avec ces critères.</p>
                    <a href="{{ route('products.index') }}" 
                       class="text-blue-600 hover:text-blue-800 font-semibold">
                        Voir tous les produits →
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($products as $product)
                        @include('products.partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
```

**💡 Points clés** :
- **Sidebar filtres** : catégorie, prix, stock, promotion, tri
- **Conservation des filtres** : `withQueryString()` dans le controller
- **Message "Aucun résultat"** : si aucun produit ne correspond aux critères
- **Pagination** : avec Laravel's default Tailwind styling

---

## 8️⃣ Création de la Page Détail Produit

### Création de `resources/views/products/show.blade.php`

```blade
@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Fil d'Ariane (Breadcrumb) -->
    <nav class="flex mb-8 text-sm" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-blue-600 transition">
                    🏠 Accueil
                </a>
            </li>
            <li>
                <span class="mx-2 text-gray-400">/</span>
            </li>
            <li>
                <a href="{{ route('categories.show', $product->category->slug) }}" 
                   class="text-gray-600 hover:text-blue-600 transition">
                    {{ $product->category->name }}
                </a>
            </li>
            <li>
                <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-900 font-semibold">
                {{ $product->name }}
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
        
        <!-- Images -->
        <div>
            <!-- Image principale -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-4">
                <img src="{{ $product->image_url }}" 
                     alt="{{ $product->name }}"
                     class="w-full h-96 object-contain">
            </div>

            <!-- Images supplémentaires (si existantes) -->
            @if($product->images && count($product->images) > 0)
                <div class="grid grid-cols-4 gap-4">
                    @foreach($product->all_image_urls as $imageUrl)
                        <div class="bg-white rounded-lg shadow p-2 hover:shadow-lg transition cursor-pointer">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-20 object-contain">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Informations -->
        <div>
            <!-- Catégorie -->
            <a href="{{ route('categories.show', $product->category->slug) }}"
               class="inline-block text-sm text-blue-600 hover:text-blue-800 mb-2 font-semibold">
                📦 {{ $product->category->name }}
            </a>

            <!-- Nom -->
            <h1 class="text-3xl font-bold mb-4">{{ $product->name }}</h1>

            <!-- Description courte -->
            @if($product->short_description)
                <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                    {{ $product->short_description }}
                </p>
            @endif

            <!-- Prix -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6 border-2 border-gray-200">
                @if($product->is_on_sale)
                    <div class="flex items-center gap-4">
                        <span class="text-4xl font-bold text-red-600">
                            {{ $product->formatted_sale_price }}
                        </span>
                        <div>
                            <span class="text-xl text-gray-500 line-through">
                                {{ $product->formatted_price }}
                            </span>
                            <span class="ml-2 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded shadow">
                                -{{ $product->discount_percentage }}%
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-green-600 mt-2 font-semibold">
                        💰 Vous économisez {{ $product->savings }}
                    </p>
                @else
                    <span class="text-4xl font-bold text-gray-900">
                        {{ $product->formatted_price }}
                    </span>
                @endif
            </div>

            <!-- Stock -->
            <div class="mb-6 p-4 rounded-lg {{ $product->in_stock ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                @if($product->in_stock)
                    <span class="inline-flex items-center text-green-700 font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        ✅ En stock ({{ $product->stock_quantity }} unités disponibles)
                    </span>
                @else
                    <span class="inline-flex items-center text-red-700 font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        ❌ Rupture de stock
                    </span>
                @endif
            </div>

            <!-- Bouton Ajouter au panier -->
            @auth
                @if($product->in_stock)
                    <form action="{{ route('cart.add', $product) }}" method="POST" class="mb-6">
                        @csrf
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition shadow-lg hover:shadow-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Ajouter au panier
                        </button>
                    </form>
                @else
                    <button disabled
                            class="w-full bg-gray-300 text-gray-600 font-bold py-4 px-8 rounded-lg text-lg cursor-not-allowed">
                        Produit indisponible
                    </button>
                @endif
            @else
                <a href="{{ route('login') }}"
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg text-lg text-center transition shadow-lg hover:shadow-xl">
                    Connectez-vous pour commander
                </a>
            @endauth

            <!-- Référence -->
            @if($product->sku)
                <p class="text-sm text-gray-500 mt-4 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                    </svg>
                    Référence : <span class="font-mono ml-1 font-semibold">{{ $product->sku }}</span>
                </p>
            @endif
        </div>
    </div>

    <!-- Description complète -->
    @if($product->description)
        <div class="bg-white rounded-lg shadow-lg p-8 mb-16">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Description du produit
            </h2>
            <div class="prose max-w-none">
                {!! $product->description !!}
            </div>
        </div>
    @endif

    <!-- Produits similaires -->
    @if($relatedProducts->isNotEmpty())
        <div>
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                Produits similaires
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    @include('products.partials.product-card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
```

**💡 Fonctionnalités de la page détail** :
- **Fil d'Ariane** : navigation hiérarchique
- **Galerie d'images** : image principale + miniatures
- **Prix** : affichage prix normal ou promo avec économies calculées
- **Stock** : badge vert (en stock) ou rouge (rupture)
- **Bouton panier** : adapté selon authentification et stock
- **Description HTML** : affichée avec le rich editor
- **Produits similaires** : 4 produits de la même catégorie

---

## 9️⃣ Création de la Page Catégorie

### Création de `resources/views/categories/show.blade.php`

Créez d'abord le dossier :
```bash
mkdir -p resources/views/categories
```

Puis créez le fichier :

```blade
@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- En-tête de la catégorie -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <div class="flex items-center gap-6">
            @if($category->image)
                <img src="{{ $category->image_url }}" 
                     alt="{{ $category->name }}"
                     class="w-24 h-24 object-cover rounded-full shadow-lg">
            @else
                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center shadow-lg">
                    <span class="text-4xl">📦</span>
                </div>
            @endif
            
            <div>
                <h1 class="text-4xl font-bold mb-2">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="text-gray-600 text-lg">{{ $category->description }}</p>
                @endif
                <p class="text-sm text-gray-500 mt-2">
                    {{ $products->total() }} produits dans cette catégorie
                </p>
            </div>
        </div>
    </div>

    <!-- Produits de la catégorie -->
    @if($products->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-500 text-lg mb-4">Aucun produit disponible dans cette catégorie pour le moment.</p>
            <a href="{{ route('products.index') }}" 
               class="text-blue-600 hover:text-blue-800 font-semibold">
                Voir tous les produits →
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            @foreach($products as $product)
                @include('products.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif

</div>
@endsection
```

**💡 Points clés** :
- **En-tête visuel** : image, nom, description de la catégorie
- **Compteur** : nombre total de produits
- **Grille responsive** : 1-4 colonnes selon la taille d'écran
- **Pagination** : navigation entre les pages

---

## 🔟 Création du Footer (optionnel)

### Création de `resources/views/layouts/footer.blade.php`

```blade
<footer class="bg-gray-800 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <!-- À propos -->
            <div>
                <h3 class="text-lg font-bold mb-4">🛒 Boutique</h3>
                <p class="text-gray-400 text-sm">
                    Votre boutique en ligne de confiance pour des produits de qualité.
                </p>
            </div>

            <!-- Liens -->
            <div>
                <h3 class="text-lg font-bold mb-4">Liens rapides</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition">Accueil</a></li>
                    <li><a href="{{ route('products.index') }}" class="text-gray-400 hover:text-white transition">Produits</a></li>
                    @auth
                        <li><a href="{{ route('cart.index') }}" class="text-gray-400 hover:text-white transition">Mon Panier</a></li>
                        <li><a href="{{ url('/customer') }}" class="text-gray-400 hover:text-white transition">Mon Espace</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-lg font-bold mb-4">Contact</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li>📧 contact@boutique.com</li>
                    <li>📞 +596 696 XX XX XX</li>
                    <li>📍 Martinique</li>
                </ul>
            </div>

            <!-- Réseaux sociaux -->
            <div>
                <h3 class="text-lg font-bold mb-4">Suivez-nous</h3>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} Boutique E-commerce. Tous droits réservés.</p>
            <p class="mt-2">Développé avec ❤️ à la Martinique - Formation CDA IMFPA</p>
        </div>
    </div>
</footer>
```

N'oubliez pas d'inclure le footer dans `layouts/app.blade.php` (déjà fait dans le code précédent).

---

## ✅ Tests et Validation

### Tests à effectuer

1. **Page d'accueil (`/`)** :
   ```bash
   php artisan serve
   ```
   - ✅ Vérifier l'affichage du hero
   - ✅ Vérifier les catégories avec images et compteurs
   - ✅ Vérifier les 3 sections (vedettes, promos, nouveautés)
   - ✅ Cliquer sur "Voir tout" pour chaque section

2. **Liste produits (`/products`)** :
   - ✅ Tester chaque filtre individuellement
   - ✅ Tester plusieurs filtres combinés
   - ✅ Tester le tri (prix, nom, date)
   - ✅ Tester la pagination
   - ✅ Vérifier le compteur de résultats
   - ✅ Réinitialiser les filtres

3. **Détail produit (`/products/{slug}`)** :
   - ✅ Vérifier toutes les informations affichées
   - ✅ Tester le bouton panier (connecté/déconnecté)
   - ✅ Vérifier les produits similaires
   - ✅ Tester le fil d'Ariane

4. **Page catégorie (`/categories/{slug}`)** :
   - ✅ Vérifier l'en-tête de catégorie
   - ✅ Vérifier les produits filtrés
   - ✅ Tester la pagination

5. **Navigation** :
   - ✅ Tester tous les liens du menu
   - ✅ Vérifier le dropdown utilisateur (si connecté)
   - ✅ Tester la version mobile (menu hamburger)

6. **Responsive** :
   - ✅ Tester sur mobile (< 640px)
   - ✅ Tester sur tablette (640-1024px)
   - ✅ Tester sur desktop (> 1024px)

---

## 📊 Checklist de Validation

- [ ] Routes créées et fonctionnelles
- [ ] Controllers créés avec méthodes complètes
- [ ] Vues Blade créées et stylisées
- [ ] Product Card réutilisable
- [ ] Filtres et tri opérationnels
- [ ] Pagination fonctionnelle
- [ ] Images affichées correctement
- [ ] Badges (promo, vedette, stock) visibles
- [ ] Bouton panier adaptatif (connecté/déconnecté)
- [ ] Design responsive
- [ ] Navigation cohérente
- [ ] Messages d'erreur/vide gérés

---

## 🎯 Points de Validation - Séance 7

- ✅ Les routes publiques fonctionnent sans erreur
- ✅ La page d'accueil affiche correctement toutes les sections
- ✅ La liste des produits avec filtres avancés fonctionne
- ✅ La page détail produit est complète et informative
- ✅ Les images s'affichent avec les bonnes proportions
- ✅ Les badges (promo, vedette, stock) apparaissent correctement
- ✅ La pagination conserve les filtres
- ✅ Le design est responsive sur tous les écrans
- ✅ La navigation est cohérente sur toutes les pages

---

## 💾 Commit Git

```bash
git add .
git commit -m "Séance 7: Frontend public avec catalogue et détail produits - HomeController, ProductController, vues Blade complètes, filtres, pagination"
git push
```

---

## 📝 Récapitulatif de la Séance

### Fichiers créés/modifiés

**Controllers** :
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/ProductController.php`

**Routes** :
- `routes/web.php` (ajout des routes publiques)

**Vues** :
- `resources/views/home.blade.php`
- `resources/views/products/index.blade.php`
- `resources/views/products/show.blade.php`
- `resources/views/products/partials/product-card.blade.php`
- `resources/views/categories/show.blade.php`
- `resources/views/layouts/navigation.blade.php` (modification)
- `resources/views/layouts/footer.blade.php` (optionnel)

### Concepts abordés

1. **Route Model Binding** : `{product:slug}` résout automatiquement le produit
2. **Query Scopes** : `active()`, `featured()`, `sorted()`
3. **Eager Loading** : `with('category')` pour éviter N+1
4. **Filtres dynamiques** : construction de requête conditionnelle
5. **Pagination** : `paginate()` avec `withQueryString()`
6. **Blade Components** : réutilisation de `product-card`
7. **Tailwind CSS** : classes utilitaires pour le design
8. **Responsive Design** : `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`

---

## 🚀 Prochaine Séance

**Séance 8 : Gestion du Panier Persistant**
- Création du modèle Cart et CartItem
- Controller de gestion du panier
- Vues du panier
- Calcul automatique des totaux
- Gestion des quantités
- Persistance en base de données

---

**🎉 Félicitations ! La Séance 7 est terminée !**

Vous avez maintenant un **frontend public complet** avec :
- Une page d'accueil attractive
- Un catalogue avec filtres avancés
- Des pages détail produit riches
- Une navigation cohérente
- Un design responsive

**Prêt pour la Séance 8 ?** 🚀