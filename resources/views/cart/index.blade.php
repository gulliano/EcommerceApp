@extends('layouts.boutique')
@section('content')

  <div class="flex h-full flex-col overflow-y-auto bg-white shadow-xl">
          <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
            <div class="flex items-start justify-between">
              <h2 id="drawer-title" class="text-lg font-medium text-gray-900">Shopping cart</h2>
              <div class="ml-3 flex h-7 items-center">
            
              </div>
            </div>

            <div class="mt-8">
              <div class="flow-root">
                <ul role="list" class="-my-6 divide-y divide-gray-200">

                   @foreach ($cart->items as $item )
                
                   
                     <li class="flex py-6">
                    <div class="size-24 shrink-0 overflow-hidden rounded-md border border-gray-200">
                      <img src="https://tailwindcss.com/plus-assets/img/ecommerce-images/shopping-cart-page-04-product-01.jpg" alt="Salmon orange fabric pouch with match zipper, gray zipper pull, and adjustable hip belt." class="size-full object-cover" />
                    </div>
         
                    <div class="ml-4 flex flex-1 flex-col">
                      <div>
                        <div class="flex justify-between text-base font-medium text-gray-900">
                          <h3>
                            <a href="#">{{$item->product->name}}</a>
                       
                          </h3>
                          <p class="ml-4">{{$item->FormattedPrice}}</p>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{$item->product->category->name}}</p>
                      </div>
                      <div class="flex flex-1 items-end justify-between text-sm">
                        <p class="text-gray-500">Qty {{$item->quantity}}</p>

                        <div class="flex">
                            <form action="{{route('cart.remove', $item)}}" method="post">
                                @csrf
                                @method('delete')

                                <button type="submit" class="font-medium text-indigo-600 hover:text-indigo-500">Remove</button>

                            </form>
                      
                        </div>
                      </div>
                    </div>
                  </li>
                   @endforeach
                 
               
                </ul>
              </div>
            </div>
          </div>

          <div class="border-t border-gray-200 px-4 py-6 sm:px-6">
            <div class="flex justify-between text-base font-medium text-gray-900">
              <p>Subtotal</p>
              <p>{{$cart->formatted_subtotal}}</p>
            </div>
            <p class="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
            <div class="mt-6">
              <a href="{{route('checkout.index')}}" class="flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-xs hover:bg-indigo-700">Checkout</a>
            </div>
              <div class="mt-6 flex justify-center text-center">
                <form action="{{route('cart.clear')}}" method="post">
                    @csrf
                    @method('delete')

                    <button type="submit" class="flex items-center justify-center rounded-md border border-transparent bg-amber-600 px-6 py-3 text-base font-medium text-white shadow-xs hover:bg-amber-700">Vider le panier</button>
                </form>
            </div> 
            <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
              <p>
                or
                <button type="button" command="close" commandfor="drawer" class="font-medium text-indigo-600 hover:text-indigo-500">
                  Continue Shopping
                  <span aria-hidden="true"> &rarr;</span>
                </button>
              </p>
            </div>
          </div>
        </div>

    
@endsection