@extends('layouts.boutique')

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