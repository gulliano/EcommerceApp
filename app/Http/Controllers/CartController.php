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
            $cartItem->incrementQuantity();
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