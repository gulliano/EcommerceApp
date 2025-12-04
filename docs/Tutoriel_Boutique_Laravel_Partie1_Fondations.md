 Tutoriel Complet : Création d'une Boutique E-commerce
## Laravel 12 + Filament 4 + Breeze Blade

---

**Formation** : CDA - Concepteur Développeur d'Applications  
**Auteur** : Gulliano - IMFPA Martinique  
**Date** : Décembre 2024  
**Durée estimée** : 8 à 10 séances  
**Niveau** : Intermédiaire

---

## 📚 Table des Matières

1. [Introduction et Prérequis](#introduction)
2. [Séance 1 : Setup du Projet](#seance-1)
3. [Séance 2 : Modèles et Migrations](#seance-2)
4. [Séance 3 : Seeders et Factories](#seance-3)
5. [Séance 4 : Panel Admin - Produits & Catégories](#seance-4)
6. [Séance 5 : Panel Admin - Commandes & Clients](#seance-5)
7. [Séance 6 : Configuration Breeze & Panel Customer](#seance-6)
8. [Séance 7 : Frontend Public - Catalogue](#seance-7)
9. [Séance 8 : Gestion du Panier Persistant](#seance-8)
10. [Séance 9 : Panel Customer - Panier & Commandes](#seance-9)
11. [Séance 10 : Dashboard Admin & Finitions](#seance-10)

---

<a name="introduction"></a>
## Introduction et Prérequis

### 🎯 Objectifs du Projet

Dans ce tutoriel progressif, vous allez développer une **boutique e-commerce complète** avec les technologies modernes du web. Ce projet vous permettra de maîtriser :

- ✅ L'architecture MVC avec Laravel 12
- ✅ La gestion d'interfaces d'administration avec Filament 4
- ✅ L'authentification multi-rôles avec Breeze
- ✅ La gestion de paniers persistants en base de données
- ✅ Les relations Eloquent complexes
- ✅ Le développement frontend avec Blade et Tailwind CSS

### 🏗️ Architecture du Projet

Le projet se compose de **trois parties principales** :

| Composant | Description | Technologie |
|-----------|-------------|-------------|
| **Frontend Public** | Catalogue produits, accès visiteurs | Breeze Blade + Tailwind |
| **Panel Customer** | Espace client : panier persistant, commandes | Filament 4 |
| **Panel Admin** | Gestion complète : produits, commandes, statistiques | Filament 4 |

### 📋 Prérequis Techniques

| Logiciel | Version Minimale | Recommandation |
|----------|------------------|----------------|
| PHP | 8.2 | PHP 8.3 |
| Composer | 2.6 | Dernière version |
| Node.js | 18.x | Node 20.x LTS |
| MySQL/MariaDB | 8.0 / 10.6 | MySQL 8.0 |
| Git | 2.30 | Dernière version |

> **💡 Note** : Ce tutoriel suppose que vous maîtrisez déjà les bases de Laravel, PHP orienté objet, et que vous avez un environnement de développement opérationnel.

---

<a name="seance-1"></a>
## Séance 1 : Setup du Projet

### 📦 Création du Projet Laravel 12

Commençons par créer un nouveau projet Laravel 12. Ouvrez votre terminal :

```bash
# Création du projet Laravel
composer create-project laravel/laravel:^12.0 boutique-ecommerce

# Déplacement dans le dossier du projet
cd boutique-ecommerce
```

Cette commande crée un nouveau projet Laravel 12 dans le dossier `boutique-ecommerce`. Le téléchargement et l'installation peuvent prendre quelques minutes.

### ⚙️ Configuration de l'Environnement

Configurez votre fichier `.env` avec les paramètres de votre base de données :

```env
# Configuration de la base de données dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boutique_ecommerce
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe

# Configuration de l'application
APP_NAME="Boutique E-commerce"
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Martinique
```

> ⚠️ **Important** : Créez la base de données `boutique_ecommerce` dans MySQL/phpMyAdmin avant de continuer.
> 
> ```sql
> CREATE DATABASE boutique_ecommerce 
> CHARACTER SET utf8mb4 
> COLLATE utf8mb4_unicode_ci;
> ```

### 🔐 Installation de Laravel Breeze

Breeze fournit l'authentification de base avec des vues Blade. Installons-le :

```bash
# Installation de Breeze
composer require laravel/breeze --dev

# Installation avec stack Blade (pas de JS framework)
php artisan breeze:install blade

# Installation des dépendances npm
npm install

# Compilation des assets
npm run dev
```

**📝 Explication** :
- `breeze:install blade` : Installe Breeze avec des templates Blade purs (sans Vue/React)
- `npm install` : Installe les dépendances JavaScript (Tailwind CSS, etc.)
- `npm run dev` : Compile les assets en mode développement

Breeze installe automatiquement :
- ✅ Les routes d'authentification (`/login`, `/register`, `/password/reset`)
- ✅ Les controllers d'authentification
- ✅ Les vues Blade avec Tailwind CSS
- ✅ La configuration Tailwind CSS

### 🎨 Installation de Filament 4

Filament est notre framework pour les panels d'administration. Installons-le :

```bash
# Installation de Filament 4
composer require filament/filament:^4.0

# Publication des assets de Filament (fonts, CSS, etc.)
php artisan filament:install
```

> 💡 **Astuce** : Filament 4 nécessite PHP 8.2 minimum et Laravel 11+. Assurez-vous que votre environnement respecte ces prérequis.
>
> ⚠️ **Important** : La commande `php artisan filament:install` publie les assets nécessaires (fonts, styles, etc.). Sans cette étape, vous aurez des erreurs 404 sur les fichiers CSS.

### 🏢 Création des Deux Panels Filament

Nous allons créer **deux panels distincts** : un pour l'administration et un pour les clients.

```bash
# Panel Admin (par défaut, accessible via /admin)
php artisan make:filament-panel admin

# Panel Customer (accessible via /customer)
php artisan make:filament-panel customer
```

Ces commandes créent deux fichiers de configuration :
- `app/Providers/Filament/AdminPanelProvider.php` - Configuration du panel admin
- `app/Providers/Filament/CustomerPanelProvider.php` - Configuration du panel client

### 🔧 Configuration des Panels

#### Panel Admin

Ouvrez `app/Providers/Filament/AdminPanelProvider.php` et configurez-le :

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')                    // Identifiant unique du panel
            ->path('admin')                  // URL d'accès : /admin
            ->login()                        // Active la page de login
            ->colors([
                'primary' => Color::Blue,    // Couleur principale
            ])
            ->brandName('Admin - Boutique')  // Nom dans la navigation
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
            ->middleware([
                'web',
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ]);
    }
}
```

**📝 Explication des paramètres** :
- `->id()` : Identifiant unique du panel (utilisé en interne)
- `->path()` : URL d'accès au panel (/admin)
- `->login()` : Active l'authentification avec page de connexion
- `->colors()` : Définit les couleurs du thème (primary, secondary, etc.)
- `->brandName()` : Nom affiché dans la barre de navigation
- `->discoverResources()` : Détecte automatiquement les ressources Filament
- `->middleware()` : Middleware appliqués au panel

#### Panel Customer

Ouvrez `app/Providers/Filament/CustomerPanelProvider.php` :

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class CustomerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('customer')                  // Identifiant unique
            ->path('customer')                // URL : /customer
            ->login()                         // Page de login
            ->colors([
                'primary' => Color::Green,    // Couleur verte pour différencier
            ])
            ->brandName('Espace Client')     // Nom affiché
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
            ->middleware([
                'web',
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ]);
    }
}
```

### ✅ Vérification de l'Installation

Testons que tout fonctionne correctement :

```bash
# Migration de la base de données
php artisan migrate

# Démarrage du serveur de développement
php artisan serve

# Dans un autre terminal, compilation des assets en mode watch
npm run dev
```

Ouvrez votre navigateur et testez les URLs suivantes :

| URL | Résultat Attendu |
|-----|------------------|
| `http://localhost:8000` | Page d'accueil Laravel |
| `http://localhost:8000/register` | Formulaire d'inscription Breeze |
| `http://localhost:8000/login` | Formulaire de connexion Breeze |
| `http://localhost:8000/admin` | Page de login Filament Admin (bleu) |
| `http://localhost:8000/customer` | Page de login Filament Customer (vert) |

### 🎯 Points de Validation - Séance 1

Vérifiez que vous avez bien :

- ✅ Le projet Laravel 12 est créé et fonctionne
- ✅ Breeze est installé avec les vues Blade
- ✅ Tailwind CSS compile correctement (`npm run dev` sans erreur)
- ✅ Filament 4 est installé
- ✅ Les deux panels (admin et customer) sont accessibles
- ✅ Les pages de login s'affichent correctement avec les bonnes couleurs
- ✅ La base de données est créée et migrée

> 💾 **Commit Git** : N'oubliez pas de commiter votre travail :
> ```bash
> git add .
> git commit -m "Séance 1: Setup projet Laravel + Filament + Breeze"
> ```

---

<a name="seance-2"></a>
## Séance 2 : Modèles et Migrations

### 🗄️ Architecture de la Base de Données

Avant de créer nos modèles, comprenons l'architecture complète de notre base de données :

| Table | Rôle | Relations |
|-------|------|-----------|
| **users** | Utilisateurs (admin + customer) | Has many: carts, orders |
| **categories** | Catégories de produits | Has many: products |
| **products** | Produits en vente | Belongs to: category |
| **carts** | Paniers clients persistants | Belongs to: user<br>Has many: cart_items |
| **cart_items** | Articles dans le panier | Belongs to: cart, product |
| **orders** | Commandes clients | Belongs to: user<br>Has many: order_items |
| **order_items** | Articles commandés | Belongs to: order, product |

**📊 Schéma relationnel** :
```
User (1) ─── (1) Cart ─── (*) CartItems ─── (*) Product
  │                                              │
  └─── (*) Orders ─── (*) OrderItems ───────────┘
                                                  │
                                            Category (1)
```

### 👤 Modification du Modèle User

#### Création de l'Enum UserRole

Commençons par créer un Enum pour gérer proprement les rôles :

```bash
# Création du fichier Enum
php artisan make:enum UserRole
```

Modifiez le fichier `app/Enums/UserRole.php` :

```php
<?php

namespace App\Enums;

/**
 * Enum pour gérer les différents rôles utilisateurs
 * 
 * - ADMIN : Accès complet au panel d'administration
 * - CUSTOMER : Accès au panel client (panier, commandes)
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';

    /**
     * Obtient le label lisible du rôle en français
     * 
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::CUSTOMER => 'Client',
        };
    }

    /**
     * Vérifie si le rôle est administrateur
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Vérifie si le rôle est client
     * 
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    /**
     * Retourne tous les rôles disponibles sous forme de tableau
     * Utile pour les formulaires de sélection
     * 
     * @return array
     */
    public static function toArray(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::CUSTOMER->value => self::CUSTOMER->label(),
        ];
    }
}
```

**📝 Pourquoi un Enum ?**
- ✅ Type-safe : évite les erreurs de typage
- ✅ Autocomplétion dans l'IDE
- ✅ Centralisé : un seul endroit pour gérer les rôles
- ✅ Méthodes helper intégrées

#### Migration pour ajouter le rôle

Créons la migration pour ajouter les champs nécessaires à la table `users` :

```bash
# Création de la migration
php artisan make:migration add_role_to_users_table --table=users
```

Modifiez `database/migrations/xxxx_xx_xx_add_role_to_users_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserRole;

return new class extends Migration
{
    /**
     * Ajoute les champs nécessaires pour la gestion des rôles
     * et les informations client
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajout du champ role avec valeur par défaut 'customer'
            $table->string('role')
                ->default(UserRole::CUSTOMER->value)
                ->after('email');
            
            // Champs additionnels pour les clients
            // Ces champs sont utiles pour les adresses de livraison
            $table->string('phone', 20)->nullable()->after('role');
            $table->text('address')->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('postal_code', 10)->nullable()->after('city');
        });
    }

    /**
     * Supprime les colonnes ajoutées lors du rollback
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 
                'phone', 
                'address', 
                'city', 
                'postal_code'
            ]);
        });
    }
};
```

#### Modification du modèle User

Modifiez `app/Models/User.php` pour intégrer le rôle et les relations :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'postal_code',
    ];

    /**
     * Attributs à cacher lors de la sérialisation
     * (pour les réponses JSON, etc.)
     * 
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting des attributs
     * Le champ 'role' sera automatiquement casté en UserRole enum
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,  // Cast automatique en Enum
        ];
    }

    // ==========================================
    // MÉTHODES HELPER POUR LES RÔLES
    // ==========================================

    /**
     * Vérifie si l'utilisateur est un administrateur
     * 
     * Usage : if ($user->isAdmin()) { ... }
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Vérifie si l'utilisateur est un client
     * 
     * Usage : if ($user->isCustomer()) { ... }
     * 
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    /**
     * Obtient le label du rôle en français
     * 
     * Usage : {{ $user->getRoleLabel() }}
     * 
     * @return string
     */
    public function getRoleLabel(): string
    {
        return $this->role->label();
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un utilisateur peut avoir un panier actif
     * Relation One-to-One
     * 
     * Usage : $user->cart
     * 
     * @return HasOne
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Un utilisateur peut avoir plusieurs commandes
     * Relation One-to-Many
     * 
     * Usage : $user->orders
     * 
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtient l'adresse complète formatée
     * Pratique pour affichage
     * 
     * Usage : {{ $user->getFullAddress() }}
     * 
     * @return string|null
     */
    public function getFullAddress(): ?string
    {
        if (!$this->address) {
            return null;
        }

        $parts = array_filter([
            $this->address,
            $this->postal_code,
            $this->city,
        ]);

        return implode(', ', $parts);
    }
}
```

**📝 Points clés** :
- Le champ `role` est automatiquement casté en `UserRole` enum
- Les méthodes `isAdmin()` et `isCustomer()` facilitent les vérifications
- Les relations `cart()` et `orders()` seront utilisées plus tard
- La méthode `getFullAddress()` formatte l'adresse pour l'affichage

### 📦 Création du Modèle Category

Créons le modèle Category avec sa migration et sa factory :

```bash
# -m : migration, -f : factory
php artisan make:model Category -mf
```

#### Migration Category

Modifiez `database/migrations/xxxx_xx_xx_create_categories_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des catégories de produits
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Nom de la catégorie (ex: "Électronique", "Vêtements")
            $table->string('name', 100);
            
            // Slug pour les URLs (ex: "electronique", "vetements")
            // Unique pour éviter les doublons
            $table->string('slug', 100)->unique();
            
            // Description optionnelle de la catégorie
            $table->text('description')->nullable();
            
            // Image de la catégorie (path relatif)
            $table->string('image')->nullable();
            
            // Pour activer/désactiver une catégorie
            // Utile pour cacher temporairement sans supprimer
            $table->boolean('is_active')->default(true);
            
            // Ordre d'affichage (pour tri manuel)
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Index pour améliorer les performances de recherche
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Supprime la table categories
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

#### Modèle Category

Modifiez `app/Models/Category.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ==========================================
    // EVENTS & OBSERVERS
    // ==========================================

    /**
     * Boot du modèle pour auto-générer le slug
     */
    protected static function boot()
    {
        parent::boot();

        // Génère automatiquement le slug avant la création
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Met à jour le slug si le nom change
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Une catégorie peut avoir plusieurs produits
     * Relation One-to-Many
     * 
     * Usage : $category->products
     * 
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Obtient uniquement les produits actifs
     * 
     * Usage : $category->activeProducts
     * 
     * @return HasMany
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    // ==========================================
    // SCOPES (Filtres réutilisables)
    // ==========================================

    /**
     * Scope pour obtenir uniquement les catégories actives
     * 
     * Usage : Category::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour trier par ordre d'affichage
     * 
     * Usage : Category::sorted()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    /**
     * Obtient l'URL complète de l'image
     * 
     * Usage : {{ $category->image_url }}
     * 
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Obtient le nombre de produits dans la catégorie
     * 
     * Usage : {{ $category->products_count }}
     * 
     * @return int
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}
```

**📝 Fonctionnalités importantes** :
- ✅ Auto-génération du slug à partir du nom
- ✅ Scopes pour filtres réutilisables (`active()`, `sorted()`)
- ✅ Accessors pour URL image et comptage produits
- ✅ Index sur slug et is_active pour performances

### 🛍️ Création du Modèle Product

```bash
php artisan make:model Product -mf
```

#### Migration Product

Modifiez `database/migrations/xxxx_xx_xx_create_products_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des produits
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Relation avec la catégorie
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();  // Si catégorie supprimée, produits aussi
            
            // Informations de base
            $table->string('name');
            $table->string('slug')->unique();
            
            // Description courte pour liste
            $table->string('short_description', 255)->nullable();
            
            // Description complète pour page détail
            $table->text('description')->nullable();
            
            // Prix
            $table->decimal('price', 10, 2);  // 10 chiffres, 2 décimales
            
            // Prix promotionnel (optionnel)
            $table->decimal('sale_price', 10, 2)->nullable();
            
            // Image principale
            $table->string('image')->nullable();
            
            // Images supplémentaires (JSON array de paths)
            $table->json('images')->nullable();
            
            // SKU (Stock Keeping Unit) - référence unique
            $table->string('sku', 50)->unique()->nullable();
            
            // Gestion du stock (même si pas demandé, bon à avoir)
            $table->integer('stock_quantity')->default(0);
            
            // Actif/Inactif
            $table->boolean('is_active')->default(true);
            
            // Produit vedette (pour mise en avant)
            $table->boolean('is_featured')->default(false);
            
            // Ordre d'affichage
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('slug');
            $table->index('category_id');
            $table->index(['is_active', 'is_featured']);
            $table->index('sku');
        });
    }

    /**
     * Supprime la table products
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

#### Modèle Product

Modifiez `app/Models/Product.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'sale_price',
        'image',
        'images',
        'sku',
        'stock_quantity',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'images' => 'array',  // JSON vers array PHP
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ==========================================
    // EVENTS & OBSERVERS
    // ==========================================

    /**
     * Boot du modèle pour auto-génération
     */
    protected static function boot()
    {
        parent::boot();

        // Génère automatiquement le slug avant la création
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            
            // Génère un SKU automatique si vide
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });

        // Met à jour le slug si le nom change
        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un produit appartient à une catégorie
     * Relation Many-to-One
     * 
     * Usage : $product->category
     * 
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Un produit peut être dans plusieurs paniers
     * 
     * Usage : $product->cartItems
     * 
     * @return HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Un produit peut être dans plusieurs commandes
     * 
     * Usage : $product->orderItems
     * 
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==========================================
    // SCOPES (Filtres réutilisables)
    // ==========================================

    /**
     * Scope pour obtenir uniquement les produits actifs
     * 
     * Usage : Product::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour obtenir les produits vedettes
     * 
     * Usage : Product::featured()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope pour filtrer par catégorie
     * 
     * Usage : Product::inCategory($categoryId)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope pour trier par prix
     * 
     * Usage : Product::sortByPrice('asc')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    // ==========================================
    // ACCESSORS & HELPER METHODS
    // ==========================================

    /**
     * Obtient le prix effectif (sale_price si existe, sinon price)
     * 
     * Usage : {{ $product->effective_price }}
     * 
     * @return float
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Vérifie si le produit est en promotion
     * 
     * Usage : @if($product->is_on_sale) ... @endif
     * 
     * @return bool
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Calcule le pourcentage de réduction
     * 
     * Usage : {{ $product->discount_percentage }}%
     * 
     * @return int|null
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->is_on_sale) {
            return null;
        }

        $discount = (($this->price - $this->sale_price) / $this->price) * 100;
        return (int) round($discount);
    }

    /**
     * Obtient l'URL complète de l'image principale
     * 
     * Usage : <img src="{{ $product->image_url }}" />
     * 
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            // Image par défaut si pas d'image
            return asset('images/no-image.png');
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Obtient toutes les URLs des images (principale + additionnelles)
     * 
     * Usage : @foreach($product->all_image_urls as $url) ... @endforeach
     * 
     * @return array
     */
    public function getAllImageUrlsAttribute(): array
    {
        $urls = [$this->image_url];

        if ($this->images) {
            foreach ($this->images as $image) {
                $urls[] = asset('storage/' . $image);
            }
        }

        return $urls;
    }

    /**
     * Vérifie si le produit est en stock
     * 
     * Usage : @if($product->in_stock) ... @endif
     * 
     * @return bool
     */
    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Formatte le prix pour l'affichage
     * 
     * Usage : {{ $product->formatted_price }}
     * 
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Formatte le prix de vente pour l'affichage
     * 
     * Usage : {{ $product->formatted_sale_price }}
     * 
     * @return string|null
     */
    public function getFormattedSalePriceAttribute(): ?string
    {
        if (!$this->sale_price) {
            return null;
        }

        return number_format($this->sale_price, 2, ',', ' ') . ' €';
    }
}
```

**📝 Fonctionnalités clés** :
- ✅ Gestion automatique du slug et SKU
- ✅ Support des images multiples (JSON)
- ✅ Prix promotionnels avec calcul de réduction
- ✅ Nombreux scopes pour filtrage facile
- ✅ Accessors pour formatage prix et URLs
- ✅ Vérification de disponibilité stock

---

**(Le tutoriel continue avec les autres modèles : Cart, CartItem, Order, OrderItem dans le fichier complet - trop long pour un seul message)**

---

### 🎯 Points de Validation - Séance 2

À la fin de cette séance, vérifiez que :

- ✅ L'Enum `UserRole` est créé et fonctionnel
- ✅ Le modèle `User` est modifié avec le rôle et les champs adresse
- ✅ Le modèle `Category` est créé avec migration et relations
- ✅ Le modèle `Product` est créé avec toutes les fonctionnalités
- ✅ Les migrations s'exécutent sans erreur : `php artisan migrate:fresh`
- ✅ Toutes les relations Eloquent sont définies correctement

> 💾 **Commit Git** :
> ```bash
> git add .
> git commit -m "Séance 2: Création modèles User, Category, Product avec relations"
> ```

---

### 📝 Exercice Pratique

Testez vos modèles dans `php artisan tinker` :

```php
// Créer une catégorie
$cat = \App\Models\Category::create([
    'name' => 'Électronique',
    'description' => 'Produits électroniques',
    'is_active' => true
]);

// Créer un produit
$prod = \App\Models\Product::create([
    'category_id' => $cat->id,
    'name' => 'iPhone 15 Pro',
    'price' => 1299.99,
    'stock_quantity' => 10,
    'is_active' => true
]);

// Tester les relations
$prod->category->name; // "Électronique"
$cat->products->count(); // 1

// Tester les scopes
\App\Models\Product::active()->count();
\App\Models\Category::sorted()->get();
```

---

<a name="seance-3"></a>
## Séance 3 : Modèles Cart, Order et Seeders

### 🛒 Création du Modèle Cart (Panier)

Le panier est persistant en base de données, permettant aux clients de retrouver leur panier après déconnexion.

```bash
php artisan make:model Cart -mf
```

#### Migration Cart

Modifiez `database/migrations/xxxx_xx_xx_create_carts_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des paniers
     * Un utilisateur = un panier actif
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // Relation avec l'utilisateur
            // Un user = un seul panier
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();  // Si user supprimé, panier aussi
            
            // Session ID pour paniers non authentifiés (fonctionnalité future)
            $table->string('session_id')->nullable()->unique();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('session_id');
        });
    }

    /**
     * Supprime la table carts
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
```

#### Modèle Cart

Modifiez `app/Models/Cart.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un panier appartient à un utilisateur
     * Relation Many-to-One
     * 
     * Usage : $cart->user
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un panier contient plusieurs articles
     * Relation One-to-Many
     * 
     * Usage : $cart->items
     * 
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // ==========================================
    // MÉTHODES HELPER
    // ==========================================

    /**
     * Calcule le montant total du panier
     * 
     * Usage : {{ $cart->total }}
     * 
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->subtotal;
        });
    }

    /**
     * Obtient le nombre total d'articles dans le panier
     * 
     * Usage : {{ $cart->total_items }}
     * 
     * @return int
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Vérifie si le panier est vide
     * 
     * Usage : @if($cart->isEmpty()) ... @endif
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Ajoute un produit au panier
     * Si le produit existe déjà, augmente la quantité
     * 
     * Usage : $cart->addItem($product, $quantity)
     * 
     * @param Product $product
     * @param int $quantity
     * @return CartItem
     */
    public function addItem(Product $product, int $quantity = 1): CartItem
    {
        // Vérifie si le produit existe déjà dans le panier
        $cartItem = $this->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            // Augmente la quantité
            $cartItem->increment('quantity', $quantity);
            $cartItem->refresh();
        } else {
            // Crée un nouvel article
            $cartItem = $this->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->effective_price,  // Prix au moment de l'ajout
            ]);
        }

        return $cartItem;
    }

    /**
     * Met à jour la quantité d'un article
     * 
     * Usage : $cart->updateItemQuantity($cartItemId, $newQuantity)
     * 
     * @param int $cartItemId
     * @param int $quantity
     * @return bool
     */
    public function updateItemQuantity(int $cartItemId, int $quantity): bool
    {
        $cartItem = $this->items()->find($cartItemId);

        if (!$cartItem) {
            return false;
        }

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $quantity]);
        }

        return true;
    }

    /**
     * Supprime un article du panier
     * 
     * Usage : $cart->removeItem($cartItemId)
     * 
     * @param int $cartItemId
     * @return bool
     */
    public function removeItem(int $cartItemId): bool
    {
        return $this->items()->where('id', $cartItemId)->delete() > 0;
    }

    /**
     * Vide complètement le panier
     * 
     * Usage : $cart->clear()
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->items()->delete();
    }

    /**
     * Formatte le total pour l'affichage
     * 
     * Usage : {{ $cart->formatted_total }}
     * 
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2, ',', ' ') . ' €';
    }
}
```

### 📦 Création du Modèle CartItem

```bash
php artisan make:model CartItem -mf
```

#### Migration CartItem

Modifiez `database/migrations/xxxx_xx_xx_create_cart_items_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des articles du panier
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            
            // Relation avec le panier
            $table->foreignId('cart_id')
                ->constrained()
                ->cascadeOnDelete();  // Si panier supprimé, items aussi
            
            // Relation avec le produit
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();  // Si produit supprimé, items aussi
            
            // Quantité d'articles
            $table->integer('quantity')->default(1);
            
            // Prix au moment de l'ajout (snapshot)
            // Important : on garde le prix pour éviter les changements de prix
            $table->decimal('price', 10, 2);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['cart_id', 'product_id']);
            
            // Contrainte d'unicité : un produit ne peut être qu'une fois dans un panier
            // Si on veut plus, on augmente la quantité
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Supprime la table cart_items
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
```

#### Modèle CartItem

Modifiez `app/Models/CartItem.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un item appartient à un panier
     * Relation Many-to-One
     * 
     * Usage : $cartItem->cart
     * 
     * @return BelongsTo
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Un item fait référence à un produit
     * Relation Many-to-One
     * 
     * Usage : $cartItem->product
     * 
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // ACCESSORS & HELPER METHODS
    // ==========================================

    /**
     * Calcule le sous-total de la ligne (prix × quantité)
     * 
     * Usage : {{ $cartItem->subtotal }}
     * 
     * @return float
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Formatte le prix pour l'affichage
     * 
     * Usage : {{ $cartItem->formatted_price }}
     * 
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Formatte le sous-total pour l'affichage
     * 
     * Usage : {{ $cartItem->formatted_subtotal }}
     * 
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' €';
    }
}
```

### 📋 Création de l'Enum OrderStatus

Créons un Enum pour gérer les statuts de commande :

```bash
php artisan make:enum OrderStatus
```

Modifiez `app/Enums/OrderStatus.php` :

```php
<?php

namespace App\Enums;

/**
 * Enum pour gérer les différents statuts de commande
 * 
 * Workflow typique :
 * PENDING → CONFIRMED → PROCESSING → SHIPPED → DELIVERED
 * ou
 * PENDING → CANCELLED
 */
enum OrderStatus: string
{
    case PENDING = 'pending';           // En attente de validation
    case CONFIRMED = 'confirmed';       // Confirmée par l'admin
    case PROCESSING = 'processing';     // En cours de préparation
    case SHIPPED = 'shipped';           // Expédiée
    case DELIVERED = 'delivered';       // Livrée
    case CANCELLED = 'cancelled';       // Annulée

    /**
     * Obtient le label lisible du statut en français
     * 
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::PROCESSING => 'En préparation',
            self::SHIPPED => 'Expédiée',
            self::DELIVERED => 'Livrée',
            self::CANCELLED => 'Annulée',
        };
    }

    /**
     * Obtient la couleur associée au statut (pour Filament)
     * 
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'success',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Obtient l'icône associée au statut (pour Filament)
     * 
     * @return string
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::PROCESSING => 'heroicon-o-cog',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    /**
     * Vérifie si le statut est modifiable
     * 
     * @return bool
     */
    public function isEditable(): bool
    {
        return !in_array($this, [self::DELIVERED, self::CANCELLED]);
    }

    /**
     * Retourne tous les statuts possibles
     * 
     * @return array
     */
    public static function toArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::CONFIRMED->value => self::CONFIRMED->label(),
            self::PROCESSING->value => self::PROCESSING->label(),
            self::SHIPPED->value => self::SHIPPED->label(),
            self::DELIVERED->value => self::DELIVERED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
```

### 📦 Création du Modèle Order

```bash
php artisan make:model Order -mf
```

#### Migration Order

Modifiez `database/migrations/xxxx_xx_xx_create_orders_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\OrderStatus;

return new class extends Migration
{
    /**
     * Crée la table des commandes
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Numéro de commande unique
            // Format : ORD-YYYYMMDD-XXXXX
            $table->string('order_number')->unique();
            
            // Relation avec l'utilisateur (client)
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();  // On ne peut pas supprimer un user avec commandes
            
            // Statut de la commande
            $table->string('status')->default(OrderStatus::PENDING->value);
            
            // Montants
            $table->decimal('subtotal', 10, 2);      // Total des articles
            $table->decimal('tax', 10, 2)->default(0);        // TVA
            $table->decimal('shipping', 10, 2)->default(0);   // Frais de port
            $table->decimal('total', 10, 2);         // Total final
            
            // Informations de livraison (snapshot au moment de la commande)
            $table->string('shipping_name');
            $table->string('shipping_email');
            $table->string('shipping_phone');
            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_postal_code');
            
            // Notes optionnelles
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Dates importantes
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('order_number');
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Supprime la table orders
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

#### Modèle Order

Modifiez `app/Models/Order.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
        'customer_notes',
        'admin_notes',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ==========================================
    // EVENTS & OBSERVERS
    // ==========================================

    /**
     * Boot du modèle pour auto-génération
     */
    protected static function boot()
    {
        parent::boot();

        // Génère automatiquement le numéro de commande
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Génère un numéro de commande unique
     * Format : ORD-YYYYMMDD-XXXXX
     * 
     * @return string
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5));
        
        $orderNumber = "ORD-{$date}-{$random}";
        
        // Vérifie l'unicité (très rare collision mais on vérifie quand même)
        while (self::where('order_number', $orderNumber)->exists()) {
            $random = strtoupper(Str::random(5));
            $orderNumber = "ORD-{$date}-{$random}";
        }
        
        return $orderNumber;
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Une commande appartient à un utilisateur
     * Relation Many-to-One
     * 
     * Usage : $order->user
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une commande contient plusieurs articles
     * Relation One-to-Many
     * 
     * Usage : $order->items
     * 
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope pour filtrer par statut
     * 
     * Usage : Order::withStatus(OrderStatus::PENDING)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param OrderStatus $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour obtenir les commandes en attente
     * 
     * Usage : Order::pending()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    // ==========================================
    // MÉTHODES DE CHANGEMENT DE STATUT
    // ==========================================

    /**
     * Confirme la commande
     * 
     * @return bool
     */
    public function confirm(): bool
    {
        if ($this->status !== OrderStatus::PENDING) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Marque la commande comme en cours de préparation
     * 
     * @return bool
     */
    public function markAsProcessing(): bool
    {
        if (!in_array($this->status, [OrderStatus::CONFIRMED])) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    /**
     * Marque la commande comme expédiée
     * 
     * @return bool
     */
    public function ship(): bool
    {
        if (!in_array($this->status, [OrderStatus::CONFIRMED, OrderStatus::PROCESSING])) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::SHIPPED,
            'shipped_at' => now(),
        ]);
    }

    /**
     * Marque la commande comme livrée
     * 
     * @return bool
     */
    public function deliver(): bool
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Annule la commande
     * 
     * @return bool
     */
    public function cancel(): bool
    {
        if ($this->status === OrderStatus::DELIVERED) {
            return false;
        }

        return $this->update([
            'status' => OrderStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    // ==========================================
    // ACCESSORS & HELPER METHODS
    // ==========================================

    /**
     * Obtient l'adresse complète formatée
     * 
     * Usage : {{ $order->full_shipping_address }}
     * 
     * @return string
     */
    public function getFullShippingAddressAttribute(): string
    {
        return "{$this->shipping_address}, {$this->shipping_postal_code} {$this->shipping_city}";
    }

    /**
     * Formatte le total pour l'affichage
     * 
     * Usage : {{ $order->formatted_total }}
     * 
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2, ',', ' ') . ' €';
    }

    /**
     * Obtient le badge de statut pour Filament
     * 
     * @return array
     */
    public function getStatusBadgeAttribute(): array
    {
        return [
            'label' => $this->status->label(),
            'color' => $this->status->color(),
            'icon' => $this->status->icon(),
        ];
    }
}
```

### 📦 Création du Modèle OrderItem

```bash
php artisan make:model OrderItem -mf
```

#### Migration OrderItem

Modifiez `database/migrations/xxxx_xx_xx_create_order_items_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des articles commandés
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // Relation avec la commande
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();  // Si commande supprimée, items aussi
            
            // Relation avec le produit
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();  // On ne peut pas supprimer un produit commandé
            
            // Snapshot des informations au moment de la commande
            // Important : on garde ces infos même si le produit change après
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            
            // Quantité commandée
            $table->integer('quantity');
            
            // Prix unitaire au moment de la commande
            $table->decimal('price', 10, 2);
            
            // Sous-total de la ligne (price × quantity)
            // Stocké pour éviter les recalculs
            $table->decimal('subtotal', 10, 2);
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Supprime la table order_items
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
```

#### Modèle OrderItem

Modifiez `app/Models/OrderItem.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'price',
        'subtotal',
    ];

    /**
     * Casting des attributs
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    // ==========================================
    // EVENTS & OBSERVERS
    // ==========================================

    /**
     * Boot du modèle pour calculs automatiques
     */
    protected static function boot()
    {
        parent::boot();

        // Calcule automatiquement le subtotal avant la sauvegarde
        static::saving(function ($orderItem) {
            $orderItem->subtotal = $orderItem->price * $orderItem->quantity;
        });
    }

    // ==========================================
    // RELATIONS ELOQUENT
    // ==========================================

    /**
     * Un item appartient à une commande
     * Relation Many-to-One
     * 
     * Usage : $orderItem->order
     * 
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Un item fait référence à un produit
     * Relation Many-to-One
     * 
     * Usage : $orderItem->product
     * 
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Formatte le prix pour l'affichage
     * 
     * Usage : {{ $orderItem->formatted_price }}
     * 
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Formatte le sous-total pour l'affichage
     * 
     * Usage : {{ $orderItem->formatted_subtotal }}
     * 
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2, ',', ' ') . ' €';
    }
}
```

### 🌱 Création des Seeders

Maintenant que tous nos modèles sont créés, créons des données de test.

#### Seeder pour les Catégories

```bash
php artisan make:seeder CategorySeeder
```

Modifiez `database/seeders/CategorySeeder.php` :

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Électronique',
                'description' => 'Smartphones, ordinateurs, tablettes et accessoires électroniques',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Vêtements',
                'description' => 'Vêtements pour hommes, femmes et enfants',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Maison & Jardin',
                'description' => 'Meubles, décoration et équipements pour la maison',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sports & Loisirs',
                'description' => 'Équipements sportifs et articles de loisirs',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Livres & Médias',
                'description' => 'Livres, films, musique et jeux vidéo',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Beauté & Santé',
                'description' => 'Produits de beauté, cosmétiques et santé',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✅ Categories created successfully!');
    }
}
```

#### Seeder pour les Utilisateurs

```bash
php artisan make:seeder UserSeeder
```

Modifiez `database/seeders/UserSeeder.php` :

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrateur principal
        User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@boutique.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);

        // Client de test
        User::create([
            'name' => 'Jean Dupont',
            'email' => 'client@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::CUSTOMER,
            'phone' => '0696123456',
            'address' => '12 Rue des Flamboyants',
            'city' => 'Fort-de-France',
            'postal_code' => '97200',
            'email_verified_at' => now(),
        ]);

        // Autres clients de test
        User::factory()
            ->count(10)
            ->customer()  // Nous allons créer ce state dans la factory
            ->create();

        $this->command->info('✅ Users created successfully!');
        $this->command->info('   Admin: admin@boutique.com / password');
        $this->command->info('   Client: client@test.com / password');
    }
}
```

#### Factory pour User

Modifiez `database/factories/UserFactory.php` pour ajouter un state "customer" :

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\UserRole;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::CUSTOMER,  // Par défaut customer
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is a customer with address
     */
    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::CUSTOMER,
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
        ]);
    }

    /**
     * Indicate that the user is an admin
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN,
        ]);
    }
}
```

#### Factory pour Category

Modifiez `database/factories/CategoryFactory.php` :

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);
        
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(15),
            'is_active' => fake()->boolean(90),  // 90% actives
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
```

#### Factory pour Product

Modifiez `database/factories/ProductFactory.php` :

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->randomFloat(2, 10, 1000);
        $hasDiscount = fake()->boolean(30);  // 30% ont une promo
        
        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(10),
            'description' => fake()->paragraphs(3, true),
            'price' => $price,
            'sale_price' => $hasDiscount ? $price * 0.8 : null,  // -20%
            'sku' => 'PRD-' . strtoupper(Str::random(8)),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(85),  // 85% actifs
            'is_featured' => fake()->boolean(20),  // 20% vedettes
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
```

#### Seeder Principal (DatabaseSeeder)

Modifiez `database/seeders/DatabaseSeeder.php` :

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
        ]);

        // Créer 50 produits après les catégories
        \App\Models\Product::factory(50)->create();

        $this->command->info('');
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Users: ' . \App\Models\User::count());
        $this->command->info('   - Categories: ' . \App\Models\Category::count());
        $this->command->info('   - Products: ' . \App\Models\Product::count());
    }
}
```

### ✅ Exécution des Migrations et Seeders

```bash
# Réinitialise la base de données et exécute toutes les migrations
php artisan migrate:fresh

# Exécute les seeders pour peupler la base
php artisan db:seed

# Ou tout en une seule commande :
php artisan migrate:fresh --seed
```

### 🎯 Points de Validation - Séance 3

Vérifiez que vous avez bien :

- ✅ Les modèles Cart et CartItem sont créés avec leurs relations
- ✅ L'Enum OrderStatus est créé avec toutes ses méthodes
- ✅ Les modèles Order et OrderItem sont créés
- ✅ Toutes les migrations s'exécutent sans erreur
- ✅ Les seeders créent des données de test
- ✅ Les factories génèrent des données cohérentes
- ✅ Vous pouvez vous connecter avec admin@boutique.com / password

### 📝 Exercice Pratique

Testez vos modèles dans `php artisan tinker` :

```php
// Tester les relations
$user = \App\Models\User::where('email', 'client@test.com')->first();
$user->cart;  // devrait être null (pas encore de panier)

// Créer un panier
$cart = $user->cart()->create();
$product = \App\Models\Product::first();
$cart->addItem($product, 2);

// Vérifier
$cart->total_items;  // 2
$cart->total;  // prix * 2
$cart->items->count();  // 1 (un seul produit, quantité 2)

// Tester les commandes
\App\Models\Order::pending()->count();
\App\Models\Order::first()->status->label();
```

> 💾 **Commit Git** :
> ```bash
> git add .
> git commit -m "Séance 3: Modèles Cart, Order + Enum OrderStatus + Seeders"
> ```

---
## 📚 Suite du Tutoriel

Ce document couvre les **fondations du projet** (Séances 1 à 3). La suite se trouve dans les documents suivants :

### 📘 Document 2 : Panels Filament (Séances 4, 5, 6)

**Contenu** :
- **Séance 4** : Panel Admin - Resources Produits & Catégories
  - Création des Resources Filament
  - Formulaires avec upload d'images
  - Tables avec filtres et actions groupées
  - Widgets de statistiques

- **Séance 5** : Panel Admin - Resources Commandes & Clients  
  - Resource Order avec gestion des statuts
  - Resource User/Customer
  - Filtrage avancé des commandes
  - Actions de changement de statut

- **Séance 6** : Configuration Breeze & Panel Customer
  - Middleware de protection par rôle
  - Configuration du panel customer
  - Navigation personnalisée par rôle

### 📗 Document 3 : Frontend & Panier (Séances 7, 8, 9, 10)

**Contenu** :
- **Séance 7** : Frontend Public - Catalogue
  - Routes et controllers
  - Vues Blade du catalogue
  - Page liste produits avec filtres
  - Page détail produit

- **Séance 8** : Gestion du Panier Persistant
  - Controller de panier
  - Sessions et authentification
  - Ajout/suppression d'articles
  - Calculs de totaux

- **Séance 9** : Panel Customer - Panier & Commandes
  - Resource panier dans panel customer
  - Création de commande depuis panier
  - Historique des commandes
  - Détails de commande

- **Séance 10** : Dashboard Admin & Finitions
  - Widgets de statistiques avancés
  - Graphiques de ventes
  - Optimisations et sécurité
  - Déploiement

---

## 🎯 Récapitulatif Document 1

### ✅ Ce que vous avez accompli

Félicitations ! Vous avez maintenant :

**Infrastructure de base** :
- ✅ Projet Laravel 12 configuré
- ✅ Breeze Blade installé et opérationnel
- ✅ Filament 4 avec 2 panels (admin + customer)
- ✅ Base de données structurée

**Modèles complets** :
- ✅ User avec système de rôles (Enum UserRole)
- ✅ Category avec auto-slug et scopes
- ✅ Product avec prix promo et images multiples
- ✅ Cart et CartItem pour panier persistant
- ✅ Order et OrderItem avec gestion de statuts (Enum OrderStatus)

**Données de test** :
- ✅ Seeders fonctionnels (Categories, Users, Products)
- ✅ Factories configurées
- ✅ 50 produits dans 6 catégories
- ✅ Comptes admin et client de test

### 🔑 Identifiants de Test

| Rôle | Email | Mot de passe | Panel |
|------|-------|--------------|-------|
| Admin | admin@boutique.com | password | /admin |
| Client | client@test.com | password | /customer |

### 📊 État de la Base de Données

```bash
# Vérifier l'état de vos données
php artisan tinker

# Comptages
\App\Models\User::count();
\App\Models\Category::count();
\App\Models\Product::count();

# Exemples de requêtes
\App\Models\Product::active()->count();
\App\Models\Product::featured()->count();
\App\Models\Category::active()->with('products')->get();
```

### 🧪 Tests de Validation

Avant de passer au Document 2, vérifiez que :

```bash
# 1. Migrations OK
php artisan migrate:fresh --seed

# 2. Serveur démarre
php artisan serve

# 3. Assets compilés
npm run dev

# 4. Accès aux panels
# - http://localhost:8000/admin (login admin)
# - http://localhost:8000/customer (login client)

# 5. Tests dans Tinker
php artisan tinker
```

**Tests à effectuer dans Tinker** :

```php
// Test des relations
$user = \App\Models\User::where('email', 'client@test.com')->first();
$user->isCustomer();  // true

$product = \App\Models\Product::first();
$product->category->name;
$product->effective_price;
$product->is_on_sale;

// Test de création de panier
$cart = $user->cart()->create();
$cart->addItem($product, 2);
$cart->total;
$cart->total_items;

// Test des Enums
\App\Enums\UserRole::toArray();
\App\Enums\OrderStatus::PENDING->label();
\App\Enums\OrderStatus::PENDING->color();
```

### 🐛 Dépannage

**Problème : Les migrations échouent**
```bash
# Vérifier la connexion DB
php artisan db:show

# Nettoyer et recommencer
php artisan migrate:fresh --seed
```

**Problème : Les images ne s'affichent pas**
```bash
# Créer le lien symbolique
php artisan storage:link

# Vérifier les permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

**Problème : Erreur "Class not found"**
```bash
# Régénérer l'autoload
composer dump-autoload

# Vider les caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Problème : npm run dev ne fonctionne pas**
```bash
# Réinstaller les dépendances
rm -rf node_modules
npm install
npm run dev
```

### 📝 Notes Pédagogiques

**Pour les formateurs** :

- **Durée estimée** : 3 séances de 3h (9h total)
- **Points de contrôle** : Fin de chaque séance avec validation
- **Exercices** : Tests dans Tinker après chaque séance
- **Difficultés courantes** :
  - Oubli du `storage:link` pour les images
  - Confusion entre les deux panels Filament
  - Erreurs de typage avec les Enums

**Pour les apprenants** :

- ✅ Prenez le temps de lire les commentaires dans le code
- ✅ Testez chaque fonctionnalité dans Tinker
- ✅ Commitez régulièrement avec Git
- ✅ N'hésitez pas à explorer la documentation Filament
- ✅ Gardez un navigateur ouvert sur localhost:8000

### 🔗 Ressources Utiles

**Documentation officielle** :
- [Laravel 12](https://laravel.com/docs/12.x)
- [Filament 4](https://filamentphp.com/docs/4.x)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Eloquent ORM](https://laravel.com/docs/12.x/eloquent)

**Pour aller plus loin** :
- [Filament Tricks](https://filamentphp.com/community)
- [Laravel Daily](https://laraveldaily.com)
- [Laracasts](https://laracasts.com)

---

## 🚀 Prochaine Étape

Passez maintenant au **Document 2 : Panels Filament (Séances 4, 5, 6)** pour créer les interfaces d'administration complètes.

Vous y apprendrez :
- 🎨 Créer des Resources Filament professionnelles
- 📋 Configurer des formulaires complexes
- 🔍 Ajouter des filtres et recherches avancées
- ⚡ Créer des actions personnalisées
- 📊 Construire des widgets de statistiques
- 🔐 Gérer les permissions par rôle

**Prêt ? C'est parti ! 🎯**

---

*Tutoriel créé par Gulliano - IMFPA Martinique - Formation CDA*  
*Document 1/3 - Mis à jour : Décembre 2024*