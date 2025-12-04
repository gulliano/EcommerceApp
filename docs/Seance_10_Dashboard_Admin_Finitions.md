# Séance 10 : Dashboard Admin & Finitions du Projet

**Formation** : CDA - Concepteur Développeur d'Applications  
**Auteur** : Gulliano - IMFPA Martinique  
**Durée** : 4 heures  
**Prérequis** : Séances 1 à 9 complétées

---

## 🎯 Objectifs de la Séance

À la fin de cette séance, vous saurez :
- ✅ Créer un dashboard admin complet avec statistiques avancées
- ✅ Développer des widgets graphiques (charts)
- ✅ Implémenter des exports Excel et PDF
- ✅ Ajouter des actions groupées avancées
- ✅ Créer des notifications système
- ✅ Améliorer les performances avec cache
- ✅ Finaliser le projet
- ✅ Générer la documentation complète

---

## 📋 Plan de la Séance

1. Dashboard Admin - Widgets statistiques avancés
2. Widgets graphiques (Charts)
3. Exports Excel/PDF des commandes
4. Actions groupées avancées
5. Système de notifications
6. Optimisations et cache
7. Configuration production
8. Documentation finale
9. Tests complets

---

## 1️⃣ Dashboard Admin - Widgets Statistiques Avancés

### Widget Statistiques Globales

```bash
php artisan make:filament-widget AdminStatsOverview --panel=admin
```

Modifiez `app/Filament/Admin/Widgets/AdminStatsOverview.php` :

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Statistiques globales
        $totalOrders = Order::count();
        $totalRevenue = Order::whereIn('status', ['CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED'])
            ->sum('total');
        $pendingOrders = Order::whereIn('status', ['PENDING', 'PROCESSING'])->count();
        $totalCustomers = User::where('role', 'CUSTOMER')->count();
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();

        // Statistiques du mois en cours
        $currentMonthOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $lastMonthOrders = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $ordersTrend = $lastMonthOrders > 0 
            ? (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 
            : 0;

        // Chiffre d'affaires du mois
        $currentMonthRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED'])
            ->sum('total');

        $lastMonthRevenue = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereIn('status', ['CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED'])
            ->sum('total');

        $revenueTrend = $lastMonthRevenue > 0 
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
            : 0;

        return [
            Stat::make('Chiffre d\'affaires total', number_format($totalRevenue, 2, ',', ' ') . ' €')
                ->description(($revenueTrend >= 0 ? '+' : '') . number_format($revenueTrend, 1) . '% ce mois')
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthRevenue * 0.7,
                    $lastMonthRevenue * 0.9,
                    $lastMonthRevenue,
                    $currentMonthRevenue * 0.8,
                    $currentMonthRevenue,
                ]),

            Stat::make('Commandes', $totalOrders)
                ->description(($ordersTrend >= 0 ? '+' : '') . number_format($ordersTrend, 1) . '% ce mois')
                ->descriptionIcon($ordersTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersTrend >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthOrders * 0.7,
                    $lastMonthOrders * 0.9,
                    $lastMonthOrders,
                    $currentMonthOrders * 0.8,
                    $currentMonthOrders,
                ]),

            Stat::make('Commandes en cours', $pendingOrders)
                ->description('À traiter')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.orders.index', ['tableFilters[in_progress][isActive]' => true])),

            Stat::make('Clients', $totalCustomers)
                ->description('Utilisateurs actifs')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart([
                    User::where('role', 'CUSTOMER')
                        ->whereDate('created_at', '>=', now()->subDays(30))
                        ->count(),
                ]),

            Stat::make('Produits', $totalProducts)
                ->description($activeProducts . ' actifs')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->url(route('filament.admin.resources.products.index')),

            Stat::make('Panier moyen', number_format($totalOrders > 0 ? $totalRevenue / $totalOrders : 0, 2, ',', ' ') . ' €')
                ->description('Valeur moyenne commande')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
```

**💡 Points clés** :
- **6 statistiques** avec tendances du mois
- **Charts** pour visualiser l'évolution
- **URLs cliquables** vers resources filtrées
- **Calculs avancés** : tendances, panier moyen

---

### Widget Graphique - Revenus par Mois

```bash
php artisan make:filament-widget OrdersChart --panel=admin
```

Modifiez `app/Filament/Admin/Widgets/OrdersChart.php` :

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Revenus des 12 derniers mois';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Trend::model(Order::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Revenus (€)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
```

**💡 Installation requise** :

```bash
composer require flowframe/laravel-trend
```

---

### Widget Graphique - Commandes par Statut

```bash
php artisan make:filament-widget OrdersByStatusChart --panel=admin
```

Modifiez `app/Filament/Admin/Widgets/OrdersByStatusChart.php` :

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Répartition des commandes par statut';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $statuses = [
            'PENDING' => 'En attente',
            'CONFIRMED' => 'Confirmées',
            'PROCESSING' => 'En préparation',
            'SHIPPED' => 'Expédiées',
            'DELIVERED' => 'Livrées',
            'CANCELLED' => 'Annulées',
        ];

        $data = [];
        $labels = [];
        $colors = [];

        $colorMap = [
            'PENDING' => '#f59e0b',
            'CONFIRMED' => '#3b82f6',
            'PROCESSING' => '#8b5cf6',
            'SHIPPED' => '#10b981',
            'DELIVERED' => '#059669',
            'CANCELLED' => '#ef4444',
        ];

        foreach ($statuses as $status => $label) {
            $count = Order::where('status', $status)->count();
            $data[] = $count;
            $labels[] = $label;
            $colors[] = $colorMap[$status];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de commandes',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
```

---

### Widget Tableau - Produits Populaires

```bash
php artisan make:filament-widget PopularProductsTable --panel=admin
```

Modifiez `app/Filament/Admin/Widgets/PopularProductsTable.php` :

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PopularProductsTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withCount(['orderItems as orders_count'])
                    ->withSum('orderItems as total_sold', 'quantity')
                    ->orderByDesc('orders_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Commandes')
                    ->badge()
                    ->color('success')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Quantité vendue')
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    })
                    ->alignCenter(),
            ])
            ->heading('Top 10 - Produits les plus vendus');
    }
}
```

---

### Configuration du Dashboard Admin

Modifiez `app/Filament/Admin/Pages/Dashboard.php` (créez-le si nécessaire) :

```php
<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Tableau de bord';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\AdminStatsOverview::class,
            \App\Filament\Admin\Widgets\OrdersChart::class,
            \App\Filament\Admin\Widgets\OrdersByStatusChart::class,
            \App\Filament\Admin\Widgets\PopularProductsTable::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }
}
```

---

## 2️⃣ Exports Excel/PDF

### Installation des dépendances

```bash
composer require pelmered/filament-excel
```

### Configuration Export dans OrderResource Admin

Modifiez `app/Filament/Admin/Resources/OrderResource.php` - ajoutez dans la méthode `table()` :

```php
use pelmered\FilamentExcel\Actions\Tables\ExportBulkAction;
use pelmered\FilamentExcel\Actions\Tables\ExportAction;
use pelmered\FilamentExcel\Exports\ExcelExport;

// Dans la méthode table(), après ->actions([...])
->headerActions([
    ExportAction::make()
        ->exports([
            ExcelExport::make()
                ->fromTable()
                ->withFilename(fn () => 'commandes-' . date('Y-m-d'))
                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                ->withColumns([
                    Column::make('order_number')->heading('N° Commande'),
                    Column::make('user.name')->heading('Client'),
                    Column::make('status')->heading('Statut'),
                    Column::make('total')->heading('Total'),
                    Column::make('created_at')->heading('Date'),
                ]),
        ]),
])
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        ExportBulkAction::make()
            ->exports([
                ExcelExport::make()
                    ->fromTable()
                    ->withFilename(fn () => 'commandes-selection-' . date('Y-m-d')),
            ]),
        
        Tables\Actions\DeleteBulkAction::make()
            ->requiresConfirmation(),
    ]),
])
```

---

## 3️⃣ Actions Groupées Avancées

### Actions sur les Commandes

Ajoutez dans `app/Filament/Admin/Resources/OrderResource.php` - dans `bulkActions()` :

```php
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

Tables\Actions\BulkActionGroup::make([
    // Actions de changement de statut
    BulkAction::make('confirm')
        ->label('Confirmer les commandes')
        ->icon('heroicon-m-check-circle')
        ->color('info')
        ->requiresConfirmation()
        ->action(function (Collection $records) {
            $records->each(function ($record) {
                if ($record->status === 'PENDING') {
                    $record->update([
                        'status' => 'CONFIRMED',
                        'confirmed_at' => now(),
                    ]);
                }
            });
        })
        ->deselectRecordsAfterCompletion()
        ->successNotificationTitle('Commandes confirmées'),

    BulkAction::make('process')
        ->label('Mettre en préparation')
        ->icon('heroicon-m-cog-6-tooth')
        ->color('primary')
        ->requiresConfirmation()
        ->action(function (Collection $records) {
            $records->each(function ($record) {
                if (in_array($record->status, ['PENDING', 'CONFIRMED'])) {
                    $record->update(['status' => 'PROCESSING']);
                }
            });
        })
        ->deselectRecordsAfterCompletion()
        ->successNotificationTitle('Commandes en préparation'),

    BulkAction::make('ship')
        ->label('Marquer comme expédiées')
        ->icon('heroicon-m-truck')
        ->color('success')
        ->requiresConfirmation()
        ->action(function (Collection $records) {
            $records->each(function ($record) {
                if (in_array($record->status, ['CONFIRMED', 'PROCESSING'])) {
                    $record->update([
                        'status' => 'SHIPPED',
                        'shipped_at' => now(),
                    ]);
                }
            });
        })
        ->deselectRecordsAfterCompletion()
        ->successNotificationTitle('Commandes expédiées'),

    // Export Excel
    ExportBulkAction::make(),
    
    // Suppression
    Tables\Actions\DeleteBulkAction::make(),
]),
```

---

## 4️⃣ Système de Notifications

### Notification lors du passage de commande

Créez une notification :

```bash
php artisan make:notification NewOrderNotification
```

Modifiez `app/Notifications/NewOrderNotification.php` :

```php
<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle commande - ' . $this->order->order_number)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous avez reçu une nouvelle commande.')
            ->line('Numéro de commande : ' . $this->order->order_number)
            ->line('Montant : ' . $this->order->formatted_total)
            ->action('Voir la commande', route('filament.admin.resources.orders.view', $this->order))
            ->line('Merci !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
        ];
    }

    public function toFilament(): FilamentNotification
    {
        return FilamentNotification::make()
            ->title('Nouvelle commande')
            ->body('Commande ' . $this->order->order_number . ' pour ' . $this->order->formatted_total)
            ->icon('heroicon-o-shopping-bag')
            ->iconColor('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Voir')
                    ->url(route('filament.admin.resources.orders.view', $this->order))
                    ->button(),
            ]);
    }
}
```

### Envoyer la notification lors du checkout

Modifiez `app/Http/Controllers/CheckoutController.php` - dans la méthode `process()` après création commande :

```php
use App\Models\User;
use App\Notifications\NewOrderNotification;

// Après : $order = Order::create([...]);

// Notifier les admins
$admins = User::where('role', 'ADMIN')->get();
foreach ($admins as $admin) {
    $admin->notify(new NewOrderNotification($order));
}
```

---

## 5️⃣ Optimisations et Cache

### Ajout de Cache pour les Statistiques

Modifiez `app/Filament/Admin/Widgets/AdminStatsOverview.php` :

```php
use Illuminate\Support\Facades\Cache;

protected function getStats(): array
{
    return Cache::remember('admin-stats-overview', now()->addMinutes(5), function () {
        // ... tout le code existant ...
    });
}
```

### Ajout d'Index pour Performance

Créez une migration :

```bash
php artisan make:migration add_indexes_for_performance
```

Modifiez la migration :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'is_featured']);
            $table->index(['category_id', 'is_active']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_featured']);
            $table->dropIndex(['category_id', 'is_active']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
    }
};
```

```bash
php artisan migrate
```

---

## 6️⃣ Configuration Production

### Fichier .env.production

Créez `.env.production` :

```env
APP_NAME="Boutique E-commerce"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://votre-domaine.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_production
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@votre-domaine.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Commandes de Déploiement

Créez `deploy.sh` :

```bash
#!/bin/bash

echo "🚀 Déploiement en cours..."

# Mise à jour du code
git pull origin main