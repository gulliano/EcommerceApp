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