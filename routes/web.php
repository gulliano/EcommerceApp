<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CartController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;


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
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add'); // ajouter un produit {product} au panier
    Route::patch('/cart/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

// Commande (Checkout )
Route::middleware(['auth'])->group(function () {

     //Route::get('/checkout', [CheckoutController::class, 'teststripe'])->name('teststripe');
     Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
     Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
     Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
     Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

});


/*
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckoutController;

Route::get('/', function () {
    return view('welcome');
});


*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
