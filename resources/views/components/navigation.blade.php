
<div class="bg-white">
  <!-- Mobile menu -->
  <el-dialog>
    <dialog id="mobile-menu" class="backdrop:bg-transparent lg:hidden">
      <el-dialog-backdrop class="fixed inset-0 bg-black/25 transition-opacity duration-300 ease-linear data-closed:opacity-0"></el-dialog-backdrop>
      <div tabindex="0" class="fixed inset-0 flex focus:outline-none">
        <el-dialog-panel class="relative flex w-full max-w-xs transform flex-col overflow-y-auto bg-white pb-12 shadow-xl transition duration-300 ease-in-out data-closed:-translate-x-full">
          <div class="flex px-4 pt-5 pb-2">
            <button type="button" command="close" commandfor="mobile-menu" class="relative -m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400">
              <span class="absolute -inset-0.5"></span>
              <span class="sr-only">Close menu</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>

    

          <div class="space-y-6 border-t border-gray-200 px-4 py-6">
            <div class="flow-root">
              <a href="{{route('home')}}" class="-m-2 block p-2 font-medium text-gray-900">Accueil</a>
            </div>
            <div class="flow-root">
              <a href="#" class="-m-2 block p-2 font-medium text-gray-900">Produit</a>
            </div>
              <div class="flow-root">
              <a href="#" class="-m-2 block p-2 font-medium text-gray-900">Catégorie</a>
            </div>
          </div>

          <div class="space-y-6 border-t border-gray-200 px-4 py-6">
            @guest
            <div class="flow-root">
              <a href="{{route('login')}}" class="-m-2 block p-2 font-medium text-gray-900">Connexion</a>
            </div>
            @endguest
           
            @auth
            <div class="flow-root">
              <a href="{{route('profile.edit')}}" class="-m-2 block p-2 font-medium text-gray-900">Mon compte</a>
            </div>
            @endauth
           
          </div>

        
        </el-dialog-panel>
      </div>
    </dialog>
  </el-dialog>

  <header class="relative bg-white">
 
    <nav aria-label="Top" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="border-b border-gray-200">
        <div class="flex h-16 items-center">
            <!-- Début bouton menu -->
          <button type="button" command="show-modal" commandfor="mobile-menu" class="relative rounded-md bg-white p-2 text-gray-400 lg:hidden">
            <span class="absolute -inset-0.5"></span>
            <span class="sr-only">Open menu</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
              <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
            <!-- Fin bouton menu -->

          <!-- Logo -->
          <div class="ml-4 flex lg:ml-0">
            <a href="{{route('home')}}">
              <span class="sr-only">Master Gugu</span>
              <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="" class="h-8 w-auto" />
            </a>
          </div>
           <!-- Fin Logo -->
           
   

          <div class="ml-auto flex items-center">
            <div class="hidden lg:flex lg:flex-1 lg:items-center lg:justify-end lg:space-x-6">
            <a href="{{route('home')}}" class="text-sm font-medium text-gray-700 hover:text-gray-800">Accueil</a>
              <span aria-hidden="true" class="h-6 w-px bg-gray-200"></span>
              <a href="#" class="text-sm font-medium text-gray-700 hover:text-gray-800">Produit</a>
              <span aria-hidden="true" class="h-6 w-px bg-gray-200"></span>
              <a href="#" class="text-sm font-medium text-gray-700 hover:text-gray-800">Catégorie</a>

              <span aria-hidden="true" class="h-6 w-px bg-gray-200"></span>
               @guest
                <a href="{{route('login')}}" class="text-sm font-medium text-gray-700 hover:text-gray-800">Connexion</a>
               @endguest
           
                @auth
                <a href="{{route('profile.edit')}}" class="text-sm font-medium text-gray-700 hover:text-gray-800">Mon compte</a> 
                @endauth
              
            </div>

    

          

            <!-- Cart -->
            <div class="ml-4 flow-root lg:ml-6">
              <a href="#" class="group -m-2 flex items-center p-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0 text-gray-400 group-hover:text-gray-500">
                  <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-gray-800">0</span>
                <span class="sr-only">items in cart, view bag</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
</div>
