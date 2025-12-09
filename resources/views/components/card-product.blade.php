
<div class="group relative">
        <img src="https://tailwindcss.com/plus-assets/img/ecommerce-images/product-page-01-related-product-01.jpg" alt="Front of men&#039;s Basic Tee in black." class="aspect-square w-full rounded-md bg-gray-200 object-cover group-hover:opacity-75 lg:aspect-auto lg:h-80" />
        <div class="mt-4 flex justify-between">
          <div>
            <h3 class="text-sm text-gray-700">
              <a href="{{route('products.show', $product->slug)}}">
                <span aria-hidden="true" class="absolute inset-0"></span>
               {{$product->name}}
              </a>
            </h3>
            <p class="mt-1 text-sm text-gray-500">{{$product->category->name}}</p>
          </div>
          <p class="text-sm font-medium text-gray-900">{{$product->formatted_price}}</p>
        </div>
      </div>