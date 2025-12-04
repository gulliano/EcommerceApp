
@extends('layouts.boutique')

@section('content')
  

    <main>
        <section class="top-bar-gradient py-12 md:py-20">
            <div class="container mx-auto px-4 flex flex-col lg:flex-row items-center justify-between">
                <div class="lg:w-1/2 mb-10 lg:mb-0">
                    <p class="text-sm font-semibold text-green-700 mb-2 flex items-center">
                        <span class="text-xl mr-2">🌿</span> The Best Online Plant Shop
                    </p>
                    <h1 class="text-5xl md:text-6xl font-extrabold text-green-900 leading-tight mb-6">
                        The Ultimate Plant Shopping Destination
                    </h1>
                    <p class="text-gray-600 mb-8 max-w-md">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore.
                    </p>
                    <div class="flex space-x-4">
                        <button class="bg-green-700 text-white font-bold py-3 px-6 rounded-full hover:bg-green-800 transition shadow-lg">
                            Shop Now
                        </button>
                        <button class="bg-white border border-gray-300 text-gray-700 font-bold py-3 px-6 rounded-full hover:bg-gray-50 transition">
                            View All Products
                        </button>
                    </div>
                </div>

                <div class="lg:w-1/2 relative">
                    <img src="https://via.placeholder.com/400x450/f0f0f0?text=Model+Woman+with+Plant" alt="Woman holding a plant" class="w-full max-w-lg mx-auto rounded-lg shadow-2xl">
                    <div class="absolute top-1/4 left-0 bg-white p-2 rounded-full text-xs font-semibold shadow-md hidden md:block">
                        Fast Delivery 🚚
                    </div>
                    <div class="absolute bottom-1/4 right-0 bg-white p-2 rounded-full text-xs font-semibold shadow-md hidden md:block">
                        Secure Payment 💳
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-green-700 text-white py-3 shadow-lg">
            <div class="container mx-auto px-4 flex justify-between overflow-x-auto whitespace-nowrap space-x-6">
                <a href="#" class="flex items-center space-x-2 font-semibold hover:text-yellow-300 transition">
                    <span class="text-xl">🌱</span><span>Outdoor Plants</span>
                </a>
                <a href="#" class="flex items-center space-x-2 font-semibold hover:text-yellow-300 transition">
                    <span class="text-xl">🖥️</span><span>Office Desk Plants</span>
                </a>
                <a href="#" class="flex items-center space-x-2 font-semibold hover:text-yellow-300 transition">
                    <span class="text-xl">🐾</span><span>Pets & Accessories</span>
                </a>
                <a href="#" class="flex items-center space-x-2 font-semibold hover:text-yellow-300 transition">
                    <span class="text-xl">🎁</span><span>Gift Plants & Combos</span>
                </a>
                <a href="#" class="flex items-center space-x-2 font-semibold hover:text-yellow-300 transition">
                    <span class="text-xl">💧</span><span>Care & Maintenance</span>
                </a>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="container mx-auto px-4 text-center">
                <p class="uppercase text-sm font-semibold text-gray-500 tracking-widest mb-1">Our Categories</p>
                <h2 class="text-4xl font-extrabold text-green-900 mb-12">
                    Shop By Category
                </h2>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                    <a href="#" class="flex flex-col items-center group">
                        <div class="w-32 h-32 md:w-40 md:h-40 overflow-hidden border-4 border-white shadow-xl group-hover:shadow-2xl transition duration-300 transform group-hover:scale-105">
                            <img src="https://via.placeholder.com/160/f0f0f0?text=Indoor" alt="Indoor Plants" class="w-full h-full category-img">
                        </div>
                        <p class="mt-4 font-semibold text-gray-700 group-hover:text-green-700">Indoor Plants</p>
                    </a>
                    <a href="#" class="flex flex-col items-center group">
                        <div class="w-32 h-32 md:w-40 md:h-40 overflow-hidden border-4 border-white shadow-xl group-hover:shadow-2xl transition duration-300 transform group-hover:scale-105">
                            <img src="https://via.placeholder.com/160/e0e0e0?text=Outdoor" alt="Outdoor Plants" class="w-full h-full category-img">
                        </div>
                        <p class="mt-4 font-semibold text-gray-700 group-hover:text-green-700">Outdoor Plants</p>
                    </a>
                    <a href="#" class="flex flex-col items-center group">
                        <div class="w-32 h-32 md:w-40 md:h-40 overflow-hidden border-4 border-white shadow-xl group-hover:shadow-2xl transition duration-300 transform group-hover:scale-105">
                            <img src="https://via.placeholder.com/160/d0d0d0?text=Office" alt="Office Desk Plants" class="w-full h-full category-img">
                        </div>
                        <p class="mt-4 font-semibold text-gray-700 group-hover:text-green-700">Office Desk Plants</p>
                    </a>
                    <a href="#" class="flex flex-col items-center group">
                        <div class="w-32 h-32 md:w-40 md:h-40 overflow-hidden border-4 border-white shadow-xl group-hover:shadow-2xl transition duration-300 transform group-hover:scale-105">
                            <img src="https://via.placeholder.com/160/c0c0c0?text=Pets" alt="Pots & Accessories" class="w-full h-full category-img">
                        </div>
                        <p class="mt-4 font-semibold text-gray-700 group-hover:text-green-700">Pets & Accessories</p>
                    </a>
                    <a href="#" class="flex flex-col items-center group">
                        <div class="w-32 h-32 md:w-40 md:h-40 overflow-hidden border-4 border-white shadow-xl group-hover:shadow-2xl transition duration-300 transform group-hover:scale-105">
                            <img src="https://via.placeholder.com/160/b0b0b0?text=Gifts" alt="Gift Plants & Combos" class="w-full h-full category-img">
                        </div>
                        <p class="mt-4 font-semibold text-gray-700 group-hover:text-green-700">Gift Plants & Combos</p>
                    </a>
                    <a href="#" class="flex flex-col items-center group">
                        <div class="w-32 h-32 md:w-40 md:h-40 overflow-hidden border-4 border-white shadow-xl group-hover:shadow-2xl transition duration-300 transform group-hover:scale-105">
                            <img src="https://via.placeholder.com/160/a0a0a0?text=Care" alt="Care & Maintenance" class="w-full h-full category-img">
                        </div>
                        <p class="mt-4 font-semibold text-gray-700 group-hover:text-green-700">Care & Maintenance</p>
                    </a>
                </div>
            </div>
        </section>

        <section class="py-16 bg-gray-100">
            <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 mb-10 md:mb-0 relative">
                    <div class="w-64 h-64 md:w-80 md:h-80 mx-auto rounded-full overflow-hidden border-8 border-white shadow-2xl">
                        <img src="https://via.placeholder.com/320x320/d1f7d1?text=Video+Placeholder" alt="A person working in a plant store" class="w-full h-full object-cover">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button class="bg-white/70 p-4 rounded-full text-green-700 text-2xl hover:bg-white transition">▶️</button>
                        </div>
                    </div>
                </div>

                <div class="md:w-1/2 md:pl-10">
                    <p class="uppercase text-sm font-semibold text-gray-500 tracking-widest mb-1">About Us</p>
                    <h2 class="text-4xl font-extrabold text-green-900 mb-6">
                        Bringing Nature Closer to Your Doorstep
                    </h2>
                    <p class="text-gray-600 mb-8">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore.
                    </p>

                    <div class="flex space-x-6">
                        <div class="bg-green-700 text-white p-4 rounded-lg text-center w-full">
                            <p class="text-3xl font-bold">20+</p>
                            <p class="text-sm">Categories</p>
                        </div>
                        <div class="bg-green-700 text-white p-4 rounded-lg text-center w-full">
                            <p class="text-3xl font-bold">6000+</p>
                            <p class="text-sm">Products</p>
                        </div>
                        <div class="bg-green-700 text-white p-4 rounded-lg text-center w-full">
                            <p class="text-3xl font-bold">99%</p>
                            <p class="text-sm">Satisfied Customer</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

@endsection
   