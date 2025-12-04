# 📚 Tutoriels E-commerce Laravel + Filament

Bienvenue dans les tutoriels complets pour créer une boutique e-commerce avec Laravel 12, Filament 4 et Breeze Blade.

## 📖 Documents Disponibles

### 📘 Document 1 : Fondations (Séances 1, 2, 3) - ✅ DISPONIBLE
**Fichier** : `01_Fondations_Setup_Modeles.md`

**Contenu** :
- ✅ Séance 1 : Setup du projet (Laravel 12 + Filament 4 + Breeze)
- ✅ Séance 2 : Modèles et Migrations (User, Category, Product)
- ✅ Séance 3 : Modèles avancés (Cart, Order + Seeders)

**Durée** : 9 heures (3 séances de 3h)

---

### 📙 Document 2 : Panels Filament (Séances 4, 5, 6) - 🚧 EN COURS
**Fichier** : `02_Panels_Filament.md` (à venir)

**Contenu prévu** :
- Séance 4 : Panel Admin - Resources Produits & Catégories
- Séance 5 : Panel Admin - Resources Commandes & Clients
- Séance 6 : Configuration Breeze & Panel Customer

**Durée** : 9 heures (3 séances de 3h)

---

### 📗 Document 3 : Frontend & Panier (Séances 7, 8, 9, 10) - ⏳ À VENIR
**Fichier** : `03_Frontend_Panier.md` (à venir)

**Contenu prévu** :
- Séance 7 : Frontend Public - Catalogue
- Séance 8 : Gestion du Panier Persistant
- Séance 9 : Panel Customer - Panier & Commandes
- Séance 10 : Dashboard Admin & Finitions

**Durée** : 12 heures (4 séances de 3h)

---

## 🎯 Objectifs du Projet

Créer une **boutique e-commerce complète** avec :

| Fonctionnalité | Technologies |
|----------------|--------------|
| Backend | Laravel 12 |
| Admin Panel | Filament 4 |
| Authentification | Breeze Blade |
| Frontend | Blade + Tailwind CSS |
| Base de données | MySQL/MariaDB |

---

## 🚀 Démarrage Rapide

### Prérequis
- PHP 8.2+
- Composer 2.6+
- Node.js 18.x+
- MySQL 8.0+

### Installation

```bash
# 1. Cloner le projet
git clone <votre-repo>
cd EcommerceApp

# 2. Installer les dépendances
composer install
npm install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données
# Créer la DB boutique_ecommerce dans MySQL
php artisan migrate:fresh --seed

# 5. Lancer le projet
php artisan serve
npm run dev
```

### Comptes de Test

| Rôle | Email | Mot de passe | Panel |
|------|-------|--------------|-------|
| Admin | admin@boutique.com | password | /admin |
| Client | client@test.com | password | /customer |

---

## 📚 Comment Utiliser ces Tutoriels

### Pour les Formateurs

1. **Préparation** : Lisez l'ensemble du document avant la séance
2. **Séance** : Suivez le tutoriel étape par étape avec les apprenants
3. **Validation** : Utilisez les points de contrôle en fin de séance
4. **Support** : Section dépannage disponible dans chaque document

### Pour les Apprenants

1. **Lecture** : Lisez chaque section avant de coder
2. **Code** : Suivez les exemples et testez au fur et à mesure
3. **Test** : Utilisez `php artisan tinker` pour tester vos modèles
4. **Commit** : Commitez régulièrement votre travail avec Git

---

## 🔗 Ressources Utiles

**Documentation Officielle** :
- [Laravel 12](https://laravel.com/docs/12.x)
- [Filament 4](https://filamentphp.com/docs/4.x)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Eloquent ORM](https://laravel.com/docs/12.x/eloquent)

**Communauté** :
- [Filament Community](https://filamentphp.com/community)
- [Laravel Daily](https://laraveldaily.com)
- [Laracasts](https://laracasts.com)

---

## 📝 Progression

- [x] Document 1 : Fondations (Séances 1-3)
- [ ] Document 2 : Panels Filament (Séances 4-6)
- [ ] Document 3 : Frontend & Panier (Séances 7-10)

---

## 🆘 Support

Pour toute question :
1. Consultez la section dépannage de chaque document
2. Vérifiez la documentation officielle
3. Contactez le formateur

---

*Tutoriels créés par Gulliano - IMFPA Martinique*  
*Formation CDA - Décembre 2024*
