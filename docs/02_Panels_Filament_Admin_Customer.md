# Tutoriel Boutique E-commerce - Partie 2
## Panels Filament Admin & Customer

---

**Formation** : CDA - Concepteur Développeur d'Applications  
**Auteur** : Gulliano - IMFPA Martinique  
**Document** : 2/3 - Panels Filament  
**Séances** : 4, 5, 6  
**Durée estimée** : 9 heures (3 séances de 3h)

---

## 📚 Prérequis

Avant de commencer ce document, vous devez avoir complété le **Document 1** avec :

- ✅ Laravel 12 + Filament 4 + Breeze installés
- ✅ Tous les modèles créés (User, Category, Product, Cart, Order, etc.)
- ✅ Migrations exécutées et base de données seedée
- ✅ Les deux panels (admin et customer) configurés
- ✅ Comptes de test fonctionnels

---

## 📑 Table des Matières

1. [Séance 4 : Panel Admin - Produits & Catégories](#seance-4)
2. [Séance 5 : Panel Admin - Commandes & Clients](#seance-5)
3. [Séance 6 : Configuration Breeze & Panel Customer](#seance-6)

---

<a name="seance-4"></a>
## Séance 4 : Panel Admin - Produits & Catégories

*[Le contenu de la Séance 4 que je t'ai déjà donné reste identique]*

### 🎯 Objectifs

- Créer les Resources Filament pour Category et Product
- Configurer formulaires avec upload d'images
- Ajouter filtres, recherches et actions groupées
- Créer un widget de statistiques

**[Contenu complet disponible dans le message précédent - Séance 4 OK]**

---

<a name="seance-5"></a>
## Séance 5 : Panel Admin - Commandes & Clients

### 🎯 Objectifs de la Séance

À la fin de cette séance, vous saurez :
- ✅ Créer une Resource pour gérer les commandes
- ✅ Afficher les relations (user, items) dans une table
- ✅ Créer des actions de changement de statut
- ✅ Ajouter un système de gestion des statuts avec historique
- ✅ Créer une Resource pour gérer les clients
- ✅ Afficher les commandes d'un client
- ✅ Créer des filtres avancés par date et statut

---

### 📦 Création de la Resource Order

Créons la Resource pour gérer les commandes :
```bash
php artisan make:filament-resource Order --panel=admin --generate
```

Modifiez `app/Filament/Admin/Resources/OrderResource.php` :
```php
<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Enums\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Commandes';
    protected static ?string $navigationGroup = 'Ventes';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'commande';
    protected static ?string $pluralModelLabel = 'commandes';

    /**
     * Formulaire (en lecture seule pour les commandes)
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informations commande')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('Numéro de commande')
                                    ->disabled(),

                                Forms\Components\Select::make('user_id')
                                    ->label('Client')
                                    ->relationship('user', 'name')
                                    ->disabled(),

                                Forms\Components\Select::make('status')
                                    ->label('Statut')
                                    ->options(OrderStatus::toArray())
                                    ->required()
                                    ->native(false)
                                    ->helperText('Utilisez les actions pour changer le statut'),

                                Forms\Components\Textarea::make('admin_notes')
                                    ->label('Notes administrateur')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->helperText('Notes internes (non visibles par le client)'),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Montants')
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Sous-total')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(),

                                Forms\Components\TextInput::make('tax')
                                    ->label('TVA')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(),

                                Forms\Components\TextInput::make('shipping')
                                    ->label('Livraison')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(),

                                Forms\Components\TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->extraAttributes(['class' => 'font-bold text-lg']),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Adresse de livraison')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_name')
                                    ->label('Nom')
                                    ->disabled(),

                                Forms\Components\TextInput::make('shipping_email')
                                    ->label('Email')
                                    ->disabled(),

                                Forms\Components\TextInput::make('shipping_phone')
                                    ->label('Téléphone')
                                    ->disabled(),

                                Forms\Components\Textarea::make('shipping_address')
                                    ->label('Adresse')
                                    ->rows(2)
                                    ->disabled(),

                                Forms\Components\TextInput::make('shipping_postal_code')
                                    ->label('Code postal')
                                    ->disabled(),

                                Forms\Components\TextInput::make('shipping_city')
                                    ->label('Ville')
                                    ->disabled(),
                            ]),

                        Forms\Components\Section::make('Dates')
                            ->schema([
                                Forms\Components\DateTimePicker::make('created_at')
                                    ->label('Créée le')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('confirmed_at')
                                    ->label('Confirmée le')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('shipped_at')
                                    ->label('Expédiée le')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('delivered_at')
                                    ->label('Livrée le')
                                    ->disabled(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    /**
     * Table avec filtres avancés
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
                    ->copyMessage('Numéro copié!'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Order $record): string => $record->user->email),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->color())
                    ->icon(fn (OrderStatus $state): string => $state->icon())
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn (Order $record): string => $record->created_at->format('d/m/Y')),

                Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Expédiée')
                    ->dateTime('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtre : Par statut
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(OrderStatus::toArray())
                    ->multiple()
                    ->indicator('Statuts'),

                // Filtre : Par date
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'À partir du ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Jusqu\'au ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),

                // Filtre : Commandes récentes
                Tables\Filters\Filter::make('recent')
                    ->label('Dernières 7 jours')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDays(7)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Action : Confirmer la commande
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::PENDING)
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->confirm();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Commande confirmée')
                            ->body('La commande a été confirmée avec succès.')
                    ),

                // Action : Marquer comme expédiée
                Tables\Actions\Action::make('ship')
                    ->label('Expédier')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (Order $record) => in_array($record->status, [OrderStatus::CONFIRMED, OrderStatus::PROCESSING]))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->ship();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Commande expédiée')
                            ->body('La commande a été marquée comme expédiée.')
                    ),

                // Action : Annuler
                Tables\Actions\Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record) => $record->status !== OrderStatus::DELIVERED)
                    ->requiresConfirmation()
                    ->modalDescription('Êtes-vous sûr de vouloir annuler cette commande ?')
                    ->action(function (Order $record) {
                        $record->cancel();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Commande annulée')
                            ->body('La commande a été annulée.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Action groupée : Confirmer les commandes
                    Tables\Actions\BulkAction::make('confirm_bulk')
                        ->label('Confirmer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === OrderStatus::PENDING) {
                                    $record->confirm();
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    /**
     * Infolist pour affichage détaillé
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Détails commande')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Numéro'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                            ->color(fn (OrderStatus $state): string => $state->color()),
                        Infolists\Components\TextEntry::make('total')
                            ->money('EUR')
                            ->weight('bold'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Articles commandés')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('Produit'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qté'),
                                Infolists\Components\TextEntry::make('price')
                                    ->money('EUR')
                                    ->label('Prix unitaire'),
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->money('EUR')
                                    ->label('Sous-total')
                                    ->weight('bold'),
                            ])
                            ->columns(4),
                    ]),

                Infolists\Components\Section::make('Adresse de livraison')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_shipping_address')
                            ->label('')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', OrderStatus::PENDING)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', OrderStatus::PENDING)->count();
        return $count > 0 ? 'warning' : 'success';
    }
}
```

**📝 Points clés** :
- ✅ Actions de changement de statut (confirmer, expédier, annuler)
- ✅ Filtres par statut et par date
- ✅ Badge qui compte les commandes en attente
- ✅ Infolist pour affichage détaillé des articles
- ✅ Actions visibles selon le statut actuel

---

### 👥 Création de la Resource User (Clients)

Créons la Resource pour gérer les clients :
```bash
php artisan make:filament-resource User --panel=admin --generate
```

Modifiez `app/Filament/Admin/Resources/UserResource.php` :
```php
schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText(fn (string $operation): string => 
                                $operation === 'edit' 
                                    ? 'Laissez vide pour conserver le mot de passe actuel' 
                                    : ''
                            ),

                        Forms\Components\Select::make('role')
                            ->label('Rôle')
                            ->options(UserRole::toArray())
                            ->default(UserRole::CUSTOMER->value)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Coordonnées')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\Textarea::make('address')
                            ->label('Adresse')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('Code postal')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(100),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (UserRole $state): string => $state->label())
                    ->color(fn (UserRole $state): string => $state === UserRole::ADMIN ? 'danger' : 'success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Commandes')
                    ->counts('orders')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtre : Par rôle
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options(UserRole::toArray())
                    ->multiple(),

                // Filtre : Avec/sans commandes
                Tables\Filters\Filter::make('has_orders')
                    ->label('Avec commandes')
                    ->query(fn ($query) => $query->has('orders'))
                    ->toggle(),

                // Filtre : Clients récents
                Tables\Filters\Filter::make('recent')
                    ->label('Inscrit récemment')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subMonth()))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('role', UserRole::CUSTOMER)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // Filtre dans la requête pour ne montrer que les clients
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('role', UserRole::CUSTOMER);
    }
}
```

---

### ✅ Test de la Séance 5

1. **Testez la Resource Order** :
   - Accédez à /admin/orders
   - Visualisez les commandes existantes
   - Testez les actions de changement de statut
   - Utilisez les filtres (par statut, par date)
   - Vérifiez l'infolist sur la page de détail

2. **Testez la Resource User** :
   - Accédez à /admin/users
   - Créez un nouveau client
   - Modifiez les informations
   - Vérifiez que seuls les clients apparaissent (pas les admins)
   - Testez les filtres

---

### 🎯 Points de Validation - Séance 5

- ✅ La Resource Order fonctionne
- ✅ Les actions de changement de statut fonctionnent
- ✅ Les filtres par statut et date fonctionnent
- ✅ L'infolist affiche correctement les articles commandés
- ✅ La Resource User affiche uniquement les clients
- ✅ Le compteur de commandes par client fonctionne
- ✅ Les badges de navigation sont corrects

> 💾 **Commit Git** :
> ```bash
> git add .
> git commit -m "Séance 5: Resources Commandes et Clients avec filtres"
> ```

---

<a name="seance-6"></a>
## Séance 6 : Configuration Breeze & Panel Customer

### 🎯 Objectifs de la Séance

À la fin de cette séance, vous saurez :
- ✅ Configurer les middlewares de protection par rôle
- ✅ Personnaliser les panels selon le rôle
- ✅ Configurer la navigation conditionnelle
- ✅ Créer des Resources dans le panel customer
- ✅ Implémenter les policies de sécurité

---

### 🔐 Création du Middleware de Protection par Rôle

Créons un middleware pour vérifier les rôles :
```bash
php artisan make:middleware EnsureUserRole
```

Modifiez `app/Http/Middleware/EnsureUserRole.php` :
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserRole;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        $requiredRole = match($role) {
            'admin' => UserRole::ADMIN,
            'customer' => UserRole::CUSTOMER,
            default => null,
        };

        if (!$requiredRole || $request->user()->role !== $requiredRole) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
```

Enregistrez le middleware dans `bootstrap/app.php` :
```php
withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ajout du middleware de rôle
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

---

### ⚙️ Configuration du Panel Admin avec Middleware

Modifiez `app/Providers/Filament/AdminPanelProvider.php` :
```php
id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('Admin - Boutique')
            ->discoverResources(
                in: app_path('Filament/Admin/Resources'),
                for: 'App\\Filament\\Admin\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Admin/Pages'),
                for: 'App\\Filament\\Admin\\Pages'
            )
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Admin/Widgets'),
                for: 'App\\Filament\\Admin\\Widgets'
            )
            ->widgets([
                // Widgets auto-découverts
            ])
            ->middleware([
                'web',
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ])
            // ✅ Ajout du middleware de vérification du rôle admin
            ->authGuard('web')
            ->authPasswordBroker('users');
    }
}
```

Pour forcer la vérification du rôle admin, ajoutez dans le boot() du modèle User :
```php
// Dans app/Models/User.php

/**
 * Vérifie si l'utilisateur peut accéder au panel admin
 */
public function canAccessPanel(\Filament\Panel $panel): bool
{
    if ($panel->getId() === 'admin') {
        return $this->role === UserRole::ADMIN;
    }

    if ($panel->getId() === 'customer') {
        return $this->role === UserRole::CUSTOMER;
    }

    return false;
}
```

---

### 🟢 Configuration du Panel Customer

Modifiez `app/Providers/Filament/CustomerPanelProvider.php` :
```php
id('customer')
            ->path('customer')
            ->login()
            ->registration()  // Active l'inscription
            ->colors([
                'primary' => Color::Green,
            ])
            ->brandName('Espace Client')
            ->discoverResources(
                in: app_path('Filament/Customer/Resources'),
                for: 'App\\Filament\\Customer\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Customer/Pages'),
                for: 'App\\Filament\\Customer\\Pages'
            )
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Customer/Widgets'),
                for: 'App\\Filament\\Customer\\Widgets'
            )
            ->middleware([
                'web',
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ])
            ->authGuard('web')
            ->authPasswordBroker('users');
    }
}
```

---

### 📋 Création d'une Resource dans le Panel Customer

Créons une Resource pour que les clients voient leurs commandes :
```bash
php artisan make:filament-resource Order --panel=customer --generate
```

Modifiez `app/Filament/Customer/Resources/OrderResource.php` :
```php
<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Enums\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Mes Commandes';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'commande';
    protected static ?string $pluralModelLabel = 'mes commandes';

    // Ne pas permettre la création
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Formulaire vide car read-only
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('N° Commande')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->color())
                    ->icon(fn (OrderStatus $state): string => $state->icon()),

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
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(OrderStatus::toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    /**
     * Filtre pour n'afficher que les commandes du client connecté
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
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

---

### ✅ Test de la Séance 6

1. **Test du panel admin** :
   - Déconnectez-vous
   - Connectez-vous avec `admin@boutique.com` / `password`
   - Vérifiez l'accès à /admin
   - Essayez d'accéder à /customer → devrait être refusé

2. **Test du panel customer** :
   - Déconnectez-vous
   - Connectez-vous avec `client@test.com` / `password`
   - Vérifiez l'accès à /customer
   - Essayez d'accéder à /admin → devrait être refusé
   - Vérifiez que seules VOS commandes apparaissent

3. **Test de l'inscription** :
   - Déconnectez-vous
   - Accédez à /customer/register
   - Créez un nouveau compte
   - Vérifiez que le rôle est automatiquement "customer"

---

### 🎯 Points de Validation - Séance 6

- ✅ Le middleware `canAccessPanel()` fonctionne
- ✅ Les admins ne peuvent accéder qu'au panel admin
- ✅ Les clients ne peuvent accéder qu'au panel customer
- ✅ L'inscription crée automatiquement des customers
- ✅ Les clients ne voient que leurs propres commandes
- ✅ Les couleurs des panels sont différentes (bleu vs vert)

> 💾 **Commit Git** :
> ```bash
> git add .
> git commit -m "Séance 6: Configuration Breeze + Panel Customer avec restrictions"
> ```

---

## 🎉 Récapitulatif Document 2

### ✅ Ce que vous avez accompli

**Séance 4** :
- ✅ Resources Category et Product avec CRUD complet
- ✅ Upload d'images avec éditeur intégré
- ✅ Filtres, recherches, actions groupées
- ✅ Widget de statistiques

**Séance 5** :
- ✅ Resource Order avec gestion des statuts
- ✅ Resource User (clients uniquement)
- ✅ Actions de changement de statut (confirmer, expédier, annuler)
- ✅ Filtres avancés par date et statut
- ✅ Infolist pour affichage détaillé

**Séance 6** :
- ✅ Middleware de protection par rôle
- ✅ Configuration des deux panels séparés
- ✅ Resource Order dans le panel customer
- ✅ Filtrage automatique par utilisateur connecté
- ✅ Système d'inscription pour les clients

---

## 🚀 Prochaine Étape

Passez maintenant au **Document 3 : Frontend & Panier (Séances 7, 8, 9, 10)** pour créer :

- Frontend public avec catalogue produits
- Système de panier persistant
- Processus de commande
- Dashboard admin avancé

---

*Tutoriel créé par Gulliano - IMFPA Martinique - Formation CDA*  
*Document 2/3 - Mis à jour : Décembre 2024*