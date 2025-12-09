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