# Séance 9 : Panel Customer - Commandes & Dashboard

**Formation** : CDA - Concepteur Développeur d'Applications  
**Auteur** : Gulliano - IMFPA Martinique  
**Durée** : 3 heures  
**Prérequis** : Séances 1 à 8 complétées

---

## 🎯 Objectifs de la Séance

À la fin de cette séance, vous saurez :
- ✅ Créer une Resource Order dans le panel customer
- ✅ Afficher l'historique des commandes du client
- ✅ Créer une page de détail de commande
- ✅ Filtrer les commandes par statut
- ✅ Créer un dashboard client personnalisé
- ✅ Afficher des statistiques utilisateur
- ✅ Intégrer le panier dans le panel customer

---

## 📋 Plan de la Séance

1. Modification de la Resource Order existante
2. Création de pages personnalisées dans le panel customer
3. Création du dashboard client
4. Création d'un widget statistiques client
5. Intégration du panier dans Filament
6. Personnalisation du panel customer
7. Tests et validation

---

## 1️⃣ Modification de la Resource Order dans Customer Panel

### Rappel : Resource Order déjà créée

Lors du **Document 2 - Séance 6**, nous avons déjà créé une Resource Order basique dans le panel customer. Nous allons maintenant l'enrichir considérablement.

Modifiez `app/Filament/Customer/Resources/OrderResource.php` :

```php
<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Mes Commandes';

    protected static ?string $modelLabel = 'commande';

    protected static ?string $pluralModelLabel = 'commandes';

    protected static ?int $navigationSort = 1;

    /**
     * Les clients ne peuvent pas créer de commandes manuellement
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Badge de navigation : nombre de commandes en attente
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', ['PENDING', 'PROCESSING'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Couleur du badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Formulaire (non utilisé car canCreate = false)
     */
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    /**
     * Table : liste des commandes
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('N° Commande')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Numéro copié !')
                    ->icon('heroicon-m-clipboard'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state)
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'CONFIRMED' => 'info',
                        'PROCESSING' => 'primary',
                        'SHIPPED' => 'success',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'PENDING' => 'heroicon-m-clock',
                        'CONFIRMED' => 'heroicon-m-check-circle',
                        'PROCESSING' => 'heroicon-m-cog-6-tooth',
                        'SHIPPED' => 'heroicon-m-truck',
                        'DELIVERED' => 'heroicon-m-check-badge',
                        'CANCELLED' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->description(fn (Order $record): string => $record->created_at->diffForHumans())
                    ->toggleable(),

                Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Expédiée le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Non expédiée'),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Livrée le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Non livrée'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'PENDING' => 'En attente',
                        'CONFIRMED' => 'Confirmée',
                        'PROCESSING' => 'En préparation',
                        'SHIPPED' => 'Expédiée',
                        'DELIVERED' => 'Livrée',
                        'CANCELLED' => 'Annulée',
                    ])
                    ->multiple()
                    ->indicator('Statut'),

                Tables\Filters\Filter::make('recent')
                    ->label('Commandes récentes (30j)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->toggle()
                    ->indicator('Récentes'),

                Tables\Filters\Filter::make('in_progress')
                    ->label('En cours')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereIn('status', ['PENDING', 'CONFIRMED', 'PROCESSING', 'SHIPPED'])
                    )
                    ->toggle()
                    ->indicator('En cours'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Voir')
                    ->icon('heroicon-m-eye'),
            ])
            ->bulkActions([
                // Pas d'actions groupées pour les clients
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->emptyStateHeading('Aucune commande')
            ->emptyStateDescription('Vous n\'avez pas encore passé de commande.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->emptyStateActions([
                Tables\Actions\Action::make('shop')
                    ->label('Voir les produits')
                    ->url(route('products.index'))
                    ->icon('heroicon-m-shopping-cart')
                    ->color('primary'),
            ]);
    }

    /**
     * Infolist : détail d'une commande
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section informations générales
                Infolists\Components\Section::make('Informations de la commande')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Numéro de commande')
                            ->copyable()
                            ->icon('heroicon-m-clipboard')
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => $state)
                            ->color(fn (string $state): string => match ($state) {
                                'PENDING' => 'warning',
                                'CONFIRMED' => 'info',
                                'PROCESSING' => 'primary',
                                'SHIPPED' => 'success',
                                'DELIVERED' => 'success',
                                'CANCELLED' => 'danger',
                                default => 'gray',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'PENDING' => 'heroicon-m-clock',
                                'CONFIRMED' => 'heroicon-m-check-circle',
                                'PROCESSING' => 'heroicon-m-cog-6-tooth',
                                'SHIPPED' => 'heroicon-m-truck',
                                'DELIVERED' => 'heroicon-m-check-badge',
                                'CANCELLED' => 'heroicon-m-x-circle',
                                default => 'heroicon-m-question-mark-circle',
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Date de commande')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('confirmed_at')
                            ->label('Confirmée le')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-m-check')
                            ->placeholder('Non confirmée')
                            ->visible(fn ($record) => $record->confirmed_at !== null),

                        Infolists\Components\TextEntry::make('shipped_at')
                            ->label('Expédiée le')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-m-truck')
                            ->placeholder('Non expédiée')
                            ->visible(fn ($record) => $record->shipped_at !== null),

                        Infolists\Components\TextEntry::make('delivered_at')
                            ->label('Livrée le')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-m-check-badge')
                            ->placeholder('Non livrée')
                            ->visible(fn ($record) => $record->delivered_at !== null),
                    ]),

                // Section montants
                Infolists\Components\Section::make('Montants')
                    ->icon('heroicon-o-currency-euro')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->label('Sous-total')
                            ->money('EUR'),

                        Infolists\Components\TextEntry::make('tax')
                            ->label('TVA (8.5%)')
                            ->money('EUR'),

                        Infolists\Components\TextEntry::make('shipping')
                            ->label('Livraison')
                            ->money('EUR')
                            ->color(fn ($state) => $state == 0 ? 'success' : 'gray'),

                        Infolists\Components\TextEntry::make('total')
                            ->label('Total')
                            ->money('EUR')
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary'),
                    ]),

                // Section adresse de livraison
                Infolists\Components\Section::make('Adresse de livraison')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('full_shipping_address')
                            ->label('Adresse complète')
                            ->columnSpanFull()
                            ->html()
                            ->icon('heroicon-m-home'),
                    ]),

                // Section articles commandés
                Infolists\Components\Section::make('Articles commandés')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->columns(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('Produit')
                                    ->weight('bold')
                                    ->columnSpan(2),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Quantité')
                                    ->badge()
                                    ->color('info'),

                                Infolists\Components\TextEntry::make('price')
                                    ->label('Prix unitaire')
                                    ->money('EUR'),

                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Sous-total')
                                    ->money('EUR')
                                    ->weight('bold')
                                    ->state(fn ($record) => $record->quantity * $record->price),
                            ]),
                    ]),
            ]);
    }

    /**
     * Filtre les commandes pour n'afficher que celles de l'utilisateur connecté
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->with(['items']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
```

**💡 Points clés de cette Resource** :
- **Badge navigation** : affiche le nombre de commandes en cours
- **Table enrichie** : statuts colorés avec icônes, badges, tooltips
- **Filtres** : par statut, récentes (30j), en cours
- **Infolist détaillé** : sections organisées (infos, montants, adresse, articles)
- **Empty state** : message + bouton vers la boutique si aucune commande
- **Sécurité** : `getEloquentQuery()` filtre par `user_id`

---

## 2️⃣ Création du Dashboard Client

### Widget Statistiques Client

```bash
php artisan make:filament-widget CustomerStatsOverview --resource=OrderResource --panel=customer
```

Modifiez `app/Filament/Customer/Widgets/CustomerStatsOverview.php` :

```php
<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        // Total des commandes
        $totalOrders = Order::where('user_id', $userId)->count();

        // Commandes en cours
        $pendingOrders = Order::where('user_id', $userId)
            ->whereIn('status', ['PENDING', 'CONFIRMED', 'PROCESSING', 'SHIPPED'])
            ->count();

        // Total dépensé
        $totalSpent = Order::where('user_id', $userId)
            ->whereIn('status', ['CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED'])
            ->sum('total');

        // Dernière commande
        $lastOrder = Order::where('user_id', $userId)
            ->latest()
            ->first();

        return [
            Stat::make('Total commandes', $totalOrders)
                ->description('Toutes vos commandes')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->chart([2, 5, 3, 8, 6, 9, $totalOrders]),

            Stat::make('En cours', $pendingOrders)
                ->description('Commandes en traitement')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->chart([1, 2, 1, 3, 2, 1, $pendingOrders]),

            Stat::make('Total dépensé', number_format($totalSpent, 2, ',', ' ') . ' €')
                ->description('Montant total de vos achats')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success')
                ->chart([100, 200, 150, 300, 250, 400, $totalSpent]),

            Stat::make('Dernière commande', $lastOrder ? $lastOrder->created_at->diffForHumans() : 'Aucune')
                ->description($lastOrder ? 'N° ' . $lastOrder->order_number : 'Passez votre première commande')
                ->descriptionIcon($lastOrder ? 'heroicon-m-check-circle' : 'heroicon-m-shopping-cart')
                ->color($lastOrder ? 'primary' : 'gray')
                ->url($lastOrder ? route('filament.customer.resources.orders.view', $lastOrder) : route('products.index')),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
```

**💡 Explication** :
- **4 statistiques** : total commandes, en cours, dépensé, dernière commande
- **Charts** : petits graphiques pour visualisation
- **Couleurs dynamiques** : changent selon les données
- **URL dernière commande** : cliquable vers détail ou boutique

---

### Widget Commandes Récentes

```bash
php artisan make:filament-widget LatestOrders --resource=OrderResource --panel=customer
```

Modifiez `app/Filament/Customer/Widgets/LatestOrders.php` :

```php
<?php

namespace App\Filament\Customer\Widgets;

use App\Filament\Customer\Resources\OrderResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('N° Commande')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->icon('heroicon-m-clipboard'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state)
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'CONFIRMED' => 'info',
                        'PROCESSING' => 'primary',
                        'SHIPPED' => 'success',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->description(fn ($record): string => $record->created_at->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }

    public function getDisplayName(): string
    {
        return 'Commandes récentes';
    }
}
```

**💡 Fonctionnalités** :
- Affiche les **5 dernières commandes**
- Table simplifiée avec actions
- Lien vers détail de chaque commande

---

### Page Dashboard personnalisée

Modifiez `app/Filament/Customer/Pages/Dashboard.php` (si pas existant, créez-le) :

```php
<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Tableau de bord';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Customer\Widgets\CustomerStatsOverview::class,
            \App\Filament\Customer\Widgets\LatestOrders::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 4,
        ];
    }
}
```

---

## 3️⃣ Intégration du Panier dans Filament Customer

### Création d'une page Panier personnalisée

```bash
php artisan make:filament-page CartPage --panel=customer
```

Modifiez `app/Filament/Customer/Pages/CartPage.php` :

```php
<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Page;

class CartPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string $view = 'filament.customer.pages.cart-page';

    protected static ?string $navigationLabel = 'Mon Panier';

    protected static ?string $title = 'Mon Panier';

    protected static ?int $navigationSort = 2;

    /**
     * Badge : nombre d'articles dans le panier
     */
    public static function getNavigationBadge(): ?string
    {
        $cart = auth()->user()->cart;
        
        if (!$cart || $cart->isEmpty()) {
            return null;
        }

        return (string) $cart->total_items;
    }

    /**
     * Couleur du badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    /**
     * Données passées à la vue
     */
    public function mount(): void
    {
        // Rafraîchit le panier à chaque affichage
    }

    /**
     * Actions disponibles dans l'en-tête
     */
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('continue_shopping')
                ->label('Continuer mes achats')
                ->icon('heroicon-m-arrow-left')
                ->url(route('products.index'))
                ->color('gray'),

            \Filament\Actions\Action::make('checkout')
                ->label('Passer commande')
                ->icon('heroicon-m-shopping-bag')
                ->url(route('checkout.index'))
                ->color('primary')
                ->visible(fn () => auth()->user()->cart && !auth()->user()->cart->isEmpty()),
        ];
    }
}
```

---

### Création de la vue du panier Filament

Créez `resources/views/filament/customer/pages/cart-page.blade.php` :

```blade
<x-filament-panels::page>
    @php
        $cart = auth()->user()->cart;
    @endphp

    @if(!$cart || $cart->isEmpty())
        <!-- Panier vide -->
        <div class="text-center py-12">
            <x-filament::icon
                icon="heroicon-o-shopping-cart"
                class="mx-auto h-16 w-16 text-gray-400 mb-4"
            />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Votre panier est vide
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Découvrez nos produits et commencez vos achats
            </p>
            <x-filament::button
                href="{{ route('products.index') }}"
                tag="a"
                color="primary"
                icon="heroicon-m-shopping-bag"
            >
                Voir les produits
            </x-filament::button>
        </div>
    @else
        <!-- Liste des articles -->
        <div class="space-y-4 mb-6">
            @foreach($cart->items as $item)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="flex gap-4">
                        <!-- Image -->
                        <img src="{{ $item->product->image_url }}" 
                             alt="{{ $item->product->name }}"
                             class="w-20 h-20 object-cover rounded-lg">

                        <!-- Détails -->
                        <div class="flex-grow">
                            <h4 class="font-semibold text-gray-900 dark:text-white">
                                {{ $item->product->name }}
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->product->category->name }}
                            </p>
                            
                            <div class="flex items-center gap-4 mt-2">
                                <span class="font-bold text-gray-900 dark:text-white">
                                    {{ $item->formatted_price }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    Quantité : {{ $item->quantity }}
                                </span>
                                <span class="font-bold text-primary-600 dark:text-primary-400">
                                    {{ $item->formatted_subtotal }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            <form action="{{ route('cart.remove', $item) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <x-filament::button
                                    type="submit"
                                    color="danger"
                                    icon="heroicon-m-trash"
                                    size="sm"
                                    onclick="return confirm('Supprimer cet article ?')"
                                >
                                    Supprimer
                                </x-filament::button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Récapitulatif -->
        <div class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Récapitulatif</h3>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span>Sous-total ({{ $cart->total_items }} articles)</span>
                    <span class="font-semibold">{{ $cart->formatted_subtotal }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>TVA (8.5%)</span>
                    <span class="font-semibold">{{ $cart->formatted_tax }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Livraison</span>
                    <span class="font-semibold">{{ $cart->formatted_shipping }}</span>
                </div>
            </div>

            <div class="border-t border-gray-300 dark:border-gray-700 pt-4 mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold">Total</span>
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $cart->formatted_total }}
                    </span>
                </div>
            </div>

            @if($cart->shipping === 0)
                <p class="text-sm text-success-600 dark:text-success-400 mb-4">
                    ✅ Livraison gratuite !
                </p>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    💡 Plus que {{ number_format(50 - $cart->subtotal, 2) }} € pour la livraison gratuite
                </p>
            @endif

            <div class="flex gap-2">
                <x-filament::button
                    href="{{ route('products.index') }}"
                    tag="a"
                    color="gray"
                    icon="heroicon-m-arrow-left"
                    class="flex-1"
                >
                    Continuer mes achats
                </x-filament::button>

                <x-filament::button
                    href="{{ route('checkout.index') }}"
                    tag="a"
                    color="primary"
                    icon="heroicon-m-shopping-bag"
                    class="flex-1"
                >
                    Passer commande
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
```

**💡 Points clés** :
- Intégration native dans le design Filament
- Utilise les composants Filament (buttons, icons, colors)
- Mode sombre supporté
- Responsive

---

## 4️⃣ Personnalisation du Panel Customer

### Configuration du CustomerPanelProvider

Modifiez `app/Providers/Filament/CustomerPanelProvider.php` :

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CustomerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('customer')
            ->path('customer')
            ->colors([
                'primary' => Color::Green,
            ])
            ->brandName('Espace Client')
            ->brandLogo(asset('images/logo.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/favicon.png'))
            ->discoverResources(in: app_path('Filament/Customer/Resources'), for: 'App\\Filament\\Customer\\Resources')
            ->discoverPages(in: app_path('Filament/Customer/Pages'), for: 'App\\Filament\\Customer\\Pages')
            ->pages([
                \App\Filament\Customer\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Customer/Widgets'), for: 'App\\Filament\\Customer\\Widgets')
            ->widgets([
                // Widgets du dashboard déjà définis dans Dashboard.php
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->registration()
            ->profile()
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Commandes',
                'Mon compte',
            ]);
    }
}
```

**💡 Personnalisations** :
- Couleur principale : **vert** (vs bleu pour admin)
- Logo et favicon personnalisés
- Inscription activée (`registration()`)
- Profil activé (`profile()`)
- Sidebar pliable
- Groupes de navigation

---

## 5️⃣ Amélioration de la Navigation

### Ajout d'un lien vers la boutique

Créez `app/Filament/Customer/Pages/ShopPage.php` :

```php
<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Page;

class ShopPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string $view = 'filament.customer.pages.shop-page';

    protected static ?string $navigationLabel = 'Boutique';

    protected static ?string $title = 'Boutique';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Achats';

    public function mount(): void
    {
        // Redirige vers la boutique publique
        redirect()->to(route('products.index'));
    }
}
```

---

## 6️⃣ Tests et Validation

### Tests à effectuer

1. **Dashboard client** :
   ```bash
   php artisan serve
   ```
   - Se connecter en tant que client
   - Accéder à `/customer`
   - Vérifier les 4 statistiques
   - Vérifier le widget commandes récentes

2. **Liste des commandes** :
   - Cliquer sur "Mes Commandes"
   - Vérifier l'affichage de toutes les commandes
   - Tester les filtres (statut, récentes, en cours)
   - Vérifier le badge navigation
   - Tester l'empty state (si aucune commande)

3. **Détail d'une commande** :
   - Cliquer sur "Voir" sur une commande
   - Vérifier toutes les sections (infos, montants, adresse, articles)
   - Vérifier les dates affichées
   - Vérifier les badges de statut

4. **Page panier Filament** :
   - Accéder à "Mon Panier" dans le menu
   - Vérifier l'affichage des articles
   - Tester la suppression d'un article
   - Vérifier le récapitulatif
   - Tester les boutons

5. **Vérifications BDD** :
   ```bash
   php artisan tinker
   ```
   ```php
   // Vérifier les commandes d'un client
   $user = \App\Models\User::find(2); // ID du client
   $user->orders()->count();
   $user->orders()->with('items')->get();
   ```

---

## ✅ Checklist de Validation

- [ ] Dashboard client affiché avec widgets
- [ ] Statistiques correctes
- [ ] Widget commandes récentes fonctionnel
- [ ] Resource Order enrichie
- [ ] Table des commandes complète
- [ ] Filtres opérationnels
- [ ] Détail commande (infolist) complet
- [ ] Page panier Filament fonctionnelle
- [ ] Badges navigation corrects
- [ ] Personnalisation panel (couleur verte)
- [ ] Responsive sur tous les écrans
- [ ] Sécurité : client voit uniquement ses données

---

## 🎯 Points de Validation - Séance 9

- ✅ Le dashboard client affiche les bonnes statistiques
- ✅ Les widgets sont responsive et bien stylisés
- ✅ La Resource Order affiche uniquement les commandes du client
- ✅ Les filtres fonctionnent correctement
- ✅ L'infolist de détail est complet et organisé
- ✅ La page panier est intégrée dans Filament
- ✅ Les badges de navigation sont dynamiques
- ✅ Le panel customer a sa propre identité visuelle
- ✅ Tous les liens fonctionnent
- ✅ Le mode sombre est supporté

---

## 💾 Commit Git

```bash
git add .
git commit -m "Séance 9: Panel Customer avancé avec dashboard, widgets, resource Order enrichie et page panier Filament"
git push
```

---

## 📝 Récapitulatif de la Séance

### Fichiers créés/modifiés

**Resource** :
- `app/Filament/Customer/Resources/OrderResource.php` (enrichie)

**Widgets** :
- `app/Filament/Customer/Widgets/CustomerStatsOverview.php`
- `app/Filament/Customer/Widgets/LatestOrders.php`

**Pages** :
- `app/Filament/Customer/Pages/Dashboard.php`
- `app/Filament/Customer/Pages/CartPage.php`

**Vues** :
- `resources/views/filament/customer/pages/cart-page.blade.php`

**Configuration** :
- `app/Providers/Filament/CustomerPanelProvider.php` (personnalisé)

### Concepts abordés

1. **Widgets Filament** : StatsOverviewWidget, TableWidget
2. **Infolists** : affichage structuré de données
3. **Pages personnalisées** : intégration de vues custom
4. **Navigation badges** : compteurs dynamiques
5. **Filtres avancés** : multiples, toggles, indicateurs
6. **Empty states** : messages et actions quand vide
7. **Personnalisation panel** : couleurs, logo, groupes
8. **Sécurité** : `getEloquentQuery()` pour filtrer par user

---

## 🚀 Prochaine Séance

**Séance 10 : Dashboard Admin & Finitions**
- Widgets statistiques avancés (graphiques)
- Dashboard admin complet
- Exports Excel/PDF
- Finalisation du projet
- Documentation
- Tests finaux

---

**🎉 Félicitations ! La Séance 9 est terminée !**

Vous avez maintenant un **panel customer complet et professionnel** avec :
- Dashboard personnalisé avec statistiques
- Historique complet des commandes
- Détails de commandes enrichis
- Intégration du panier dans Filament
- Widgets de visualisation
- Navigation intuitive avec badges

**Prêt pour la dernière séance ?** 🚀