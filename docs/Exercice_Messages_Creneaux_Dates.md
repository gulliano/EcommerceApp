# Exercice : Affichage de Messages selon un Créneau de Date

## Objectifs Pédagogiques

À la fin de cet exercice, le stagiaire sera capable de :
- Créer une migration et un modèle pour gérer des messages temporaires
- Implémenter une logique métier pour filtrer les messages actifs par date
- Utiliser les scopes Eloquent pour simplifier les requêtes
- Intégrer un composant Filament pour gérer les messages en back-office
- Afficher les messages dynamiquement dans la barre de navigation

## Contexte Métier

L'équipe e-commerce souhaite afficher des messages promotionnels ou informatifs qui ne sont visibles que pendant une période définie. Par exemple :
- "Promotion Black Friday" du 25/11 au 30/11
- "Frais de port offerts jusqu'au 15/12"
- "Maintenance prévue le 20/12 de 02h à 04h"

---

## Partie 1 : Créer la Migration et le Modèle

### Étape 1.1 : Générer la migration

Exécute la commande pour créer une migration :

```bash
php artisan make:migration create_promotional_messages_table
```

### Étape 1.2 : Écrire la migration

Ouvre le fichier généré dans `database/migrations/` et complète-le :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotional_messages', function (Blueprint $table) {
            $table->id();
            
            // Contenu du message
            $table->string('title');
            $table->text('content');
            
            // Dates d'affichage
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            
            // Style du message (pour le rendu frontend)
            $table->string('type')->default('info'); // 'info', 'warning', 'success', 'error'
            
            // Activation/Désactivation manuelle
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotional_messages');
    }
};
```

**Explications :**
- `title` : Le titre du message (affiché en gras)
- `content` : Le contenu complet du message
- `start_date` / `end_date` : Les dates limites du créneau d'affichage
- `type` : Permet de styliser différemment selon le type (couleurs différentes en CSS)
- `is_active` : Permet de désactiver manuellement un message sans le supprimer
- `timestamps()` : Ajoute `created_at` et `updated_at`

### Étape 1.3 : Générer le Modèle

```bash
php artisan make:model PromotionalMessage
```

### Étape 1.4 : Écrire le Modèle avec les Scopes

Ouvre `app/Models/PromotionalMessage.php` et écris :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionalMessage extends Model
{
    /**
     * Les attributs assignables en masse
     */
    protected $fillable = [
        'title',
        'content',
        'start_date',
        'end_date',
        'type',
        'is_active',
    ];

    /**
     * Les attributs à convertir en date
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * SCOPE : Récupère uniquement les messages actifs
     * Utilisation : PromotionalMessage::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * SCOPE : Récupère uniquement les messages dans le créneau de date actuel
     * Utilisation : PromotionalMessage::activeNow()->get()
     */
    public function scopeActiveNow($query)
    {
        $now = now();
        
        return $query
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * SCOPE : Récupère les messages pour une date spécifique
     * Utilisation : PromotionalMessage::activeOnDate('2025-12-25')->get()
     */
    public function scopeActiveOnDate($query, $date)
    {
        $dateToCheck = \Carbon\Carbon::parse($date);
        
        return $query
            ->where('is_active', true)
            ->where('start_date', '<=', $dateToCheck)
            ->where('end_date', '>=', $dateToCheck);
    }
}
```

**Explications des Scopes :**
- Les **scopes** sont des méthodes qui permettent de réutiliser des conditions de filtrage
- `scopeActive()` : filtre les messages actifs
- `scopeActiveNow()` : filtre les messages actifs ET dans le créneau actuel
- `scopeActiveOnDate()` : filtre pour une date spécifique (utile pour les tests)

---

## Partie 2 : Créer un Resource Filament pour la Gestion

### Étape 2.1 : Générer le Resource Filament

```bash
php artisan make:filament-resource PromotionalMessage
```

### Étape 2.2 : Configurer le Resource

Ouvre `app/Filament/Resources/PromotionalMessageResource.php` et configure-le :

```php
<?php

namespace App\Filament\Resources;

use App\Models\PromotionalMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionalMessageResource extends Resource
{
    protected static ?string $model = PromotionalMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Messages Promotionnels';
    protected static ?string $modelLabel = 'Message Promotionnel';
    protected static ?string $pluralModelLabel = 'Messages Promotionnels';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contenu du Message')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Créneau de Diffusion')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Date de début')
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Date de fin')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type de message')
                            ->options([
                                'info' => 'Information',
                                'warning' => 'Avertissement',
                                'success' => 'Succès',
                                'error' => 'Erreur',
                            ])
                            ->default('info'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Message actif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'info' => 'blue',
                        'warning' => 'yellow',
                        'success' => 'green',
                        'error' => 'red',
                    }),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filtrer par type')
                    ->options([
                        'info' => 'Information',
                        'warning' => 'Avertissement',
                        'success' => 'Succès',
                        'error' => 'Erreur',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PromotionalMessageResource\Pages\ListPromotionalMessages::class,
            'create' => \App\Filament\Resources\PromotionalMessageResource\Pages\CreatePromotionalMessage::class,
            'edit' => \App\Filament\Resources\PromotionalMessageResource\Pages\EditPromotionalMessage::class,
        ];
    }
}
```

---

## Partie 3 : Créer un Composant Blade pour Afficher les Messages

### Étape 3.1 : Créer un Composant Blade

Crée un nouveau fichier : `resources/views/components/promotional-banner.blade.php`

```php
@if($message)
    <div class="promotional-banner promotional-banner--{{ $message->type }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <strong class="promotional-banner__title">
                        {{ $message->title }}
                    </strong>
                    <p class="promotional-banner__content">
                        {!! $message->content !!}
                    </p>
                </div>
                <button 
                    @click="dismissBanner('{{ $message->id }}')"
                    class="promotional-banner__close"
                    title="Fermer"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
@endif

<style>
    .promotional-banner {
        transition: all 0.3s ease;
    }

    .promotional-banner--info {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        color: #1565c0;
    }

    .promotional-banner--warning {
        background-color: #fff3e0;
        border-left: 4px solid #ff9800;
        color: #e65100;
    }

    .promotional-banner--success {
        background-color: #e8f5e9;
        border-left: 4px solid #4caf50;
        color: #2e7d32;
    }

    .promotional-banner--error {
        background-color: #ffebee;
        border-left: 4px solid #f44336;
        color: #b71c1c;
    }

    .promotional-banner__title {
        display: block;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .promotional-banner__content {
        margin: 0;
        font-size: 0.875rem;
    }

    .promotional-banner__close {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.5rem;
        padding: 0;
        margin-left: 1rem;
        flex-shrink: 0;
    }

    .promotional-banner__close:hover {
        opacity: 0.7;
    }
</style>
```

### Étape 3.2 : Créer un Controller pour gérer la logique

Crée `app/Http/Controllers/PromotionalMessageController.php` :

```php
<?php

namespace App\Http\Controllers;

use App\Models\PromotionalMessage;

class PromotionalMessageController extends Controller
{
    /**
     * Récupère le premier message actif à afficher dans la navigation
     * Affiche un seul message à la fois
     * 
     * Les messages les plus récents sont prioritaires
     */
    public static function getActiveMessage()
    {
        return PromotionalMessage::activeNow()
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
```

---

## Partie 4 : Intégrer dans la Navigation

### Étape 4.1 : Modifier le Layout Principal

Ouvre `resources/views/layouts/app.blade.php` et ajoute l'import du controller en haut :

```php
<?php
use App\Http\Controllers\PromotionalMessageController;
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- ... code existant ... --}}
    </head>
    <body class="font-sans antialiased">
        {{-- Afficher le message promotionnel actif avant la navigation --}}
        <x-promotional-banner :message="PromotionalMessageController::getActiveMessage()" />
        
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            {{-- ... reste du code ... --}}
        </div>
    </body>
</html>
```

### Étape 4.2 : Alternative - Utiliser un View Composer

Pour une approche plus élégante, crée un View Composer. Crée `app/View/Composers/PromotionalMessageComposer.php` :

```php
<?php

namespace App\View\Composers;

use App\Models\PromotionalMessage;
use Illuminate\View\View;

class PromotionalMessageComposer
{
    public function compose(View $view)
    {
        $message = PromotionalMessage::activeNow()
            ->orderBy('created_at', 'desc')
            ->first();
        
        $view->with('promotionalMessage', $message);
    }
}
```

Enregistre le composer dans `app/Providers/ViewServiceProvider.php` :

```php
<?php

namespace App\Providers;

use App\View\Composers\PromotionalMessageComposer;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Compose la view 'layouts.app' avec le message promotionnel actif
        view()->composer('layouts.app', PromotionalMessageComposer::class);
    }
}
```

Puis dans le layout, utilise simplement :

```php
<x-promotional-banner :message="$promotionalMessage" />
```

---

## Partie 5 : Créer des Seeds pour Tester

### Étape 5.1 : Créer une Seeder

```bash
php artisan make:seeder PromotionalMessageSeeder
```

### Étape 5.2 : Écrire la Seeder

Ouvre `database/seeders/PromotionalMessageSeeder.php` :

```php
<?php

namespace Database\Seeders;

use App\Models\PromotionalMessage;
use Illuminate\Database\Seeder;

class PromotionalMessageSeeder extends Seeder
{
    public function run(): void
    {
        // Message actif aujourd'hui
        PromotionalMessage::create([
            'title' => '⚡ Promotion Flash',
            'content' => 'Profitez de 20% de réduction sur tous les articles jusqu\'à demain !',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'type' => 'success',
            'is_active' => true,
        ]);

        // Message futur (pas encore visible)
        PromotionalMessage::create([
            'title' => '🎉 Black Friday',
            'content' => 'Attendez le Black Friday pour des réductions incroyables jusqu\'à 70% !',
            'start_date' => now()->addMonths(2),
            'end_date' => now()->addMonths(2)->addDays(5),
            'type' => 'warning',
            'is_active' => true,
        ]);

        // Message passé (non visible)
        PromotionalMessage::create([
            'title' => '📦 Livraison Gratuite',
            'content' => 'Offre terminée : la livraison était gratuite du 1er au 10 décembre.',
            'start_date' => now()->subMonths(1),
            'end_date' => now()->subDays(5),
            'type' => 'info',
            'is_active' => true,
        ]);

        // Message inactif (désactivé manuellement)
        PromotionalMessage::create([
            'title' => '🚫 Maintenance',
            'content' => 'Le site sera en maintenance demain de 02h à 04h.',
            'start_date' => now(),
            'end_date' => now()->addDays(2),
            'type' => 'error',
            'is_active' => false, // Désactivé
        ]);
    }
}
```

Exécute la seeder :

```bash
php artisan db:seed --class=PromotionalMessageSeeder
```

---

## Partie 6 : Tests Unitaires (Bonus)

Crée `tests/Unit/PromotionalMessageTest.php` :

```php
<?php

namespace Tests\Unit;

use App\Models\PromotionalMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionalMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_active_now_returns_only_current_message()
    {
        // Message actif maintenant
        PromotionalMessage::create([
            'title' => 'Message Actif',
            'content' => 'Test',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'type' => 'info',
            'is_active' => true,
        ]);

        // Message futur
        PromotionalMessage::create([
            'title' => 'Message Futur',
            'content' => 'Test',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'type' => 'info',
            'is_active' => true,
        ]);

        $activeMessage = PromotionalMessage::activeNow()
            ->orderBy('created_at', 'desc')
            ->first();

        $this->assertNotNull($activeMessage);
        $this->assertEquals('Message Actif', $activeMessage->title);
    }

    public function test_scope_active_now_ignores_inactive_messages()
    {
        PromotionalMessage::create([
            'title' => 'Message Inactif',
            'content' => 'Test',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'type' => 'info',
            'is_active' => false,
        ]);

        $activeMessage = PromotionalMessage::activeNow()
            ->orderBy('created_at', 'desc')
            ->first();

        $this->assertNull($activeMessage);
    }

    public function test_latest_message_has_priority()
    {
        // Deux messages actifs à la même période
        PromotionalMessage::create([
            'title' => 'Message Ancien',
            'content' => 'Test',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'type' => 'info',
            'is_active' => true,
            'created_at' => now()->subHour(),
        ]);

        PromotionalMessage::create([
            'title' => 'Message Récent',
            'content' => 'Test',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'type' => 'info',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $activeMessage = PromotionalMessage::activeNow()
            ->orderBy('created_at', 'desc')
            ->first();

        $this->assertEquals('Message Récent', $activeMessage->title);
    }
}
```

Exécute les tests :

```bash
php artisan test
```

---

## Challenges Bonus pour Approfondir

### Challenge 1 : Limitation par Utilisateur
Ajoute une colonne `display_for_users` (enum: 'all', 'authenticated', 'guests') pour montrer les messages à des catégories d'utilisateurs spécifiques.

### Challenge 2 : Compteur et Analytics
Ajoute un système de comptage pour suivre combien de fois un message a été vu et fermé.

### Challenge 3 : Animation de Fermeture
Utilise AJAX pour permettre au utilisateur de fermer un message sans recharger la page, et persiste cette préférence en session.

### Challenge 4 : Messages Stickés en Haut
Ajoute une priorité aux messages pour que certains restent toujours en haut même lors du scroll.

### Challenge 5 : Internationalisation
Fais en sorte que chaque message puisse avoir plusieurs traductions (titre et contenu en français, anglais, etc.).

---

## Correction Complète (Disponible à la Demande)

Les fichiers complètement remplis sont disponibles dans le dossier `/solutions/` si les stagiaires sont bloqués.

**Fichiers à vérifier :**
- ✅ Migration
- ✅ Modèle avec scopes
- ✅ Resource Filament
- ✅ Composant Blade
- ✅ Integration dans le layout
- ✅ Seeder de test
- ✅ Tests unitaires
