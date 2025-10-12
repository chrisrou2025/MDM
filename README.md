# Gestion des Produits - Micro Market (MDM)

## Description du Projet

Application web de gestion de produits pour un micro-marché permettant l'importation/exportation de données via fichiers Excel, la gestion des statuts produits, et la génération automatique de bons de commande.

## Technologies Utilisées

- **Backend** : PHP 8.3+
- **Base de données** : MySQL 9.1
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Bibliothèques PHP** :
  - PHPSpreadsheet 4.1 (gestion Excel)
  - TCPDF 6.9 (génération PDF)
- **Serveur web** : Apache (WAMP)

## Structure du Projet

```
MDM/
├── api/
│   ├── database.php       # Connexion base de données
│   ├── product.php        # API REST produits
│   ├── import.php         # Import fichiers XLSX
│   ├── export.php         # Export et génération PDF
│   ├── post.php           # Exemple requête POST
│   ├── put.php            # Exemple requête PUT
│   └── dupe.php           # Exemple duplication
├── assets/                # Images produits
├── archives/              # Fichiers XLSX archivés
├── commandes/             # Bons de commande PDF
├── css/
│   └── styles.css         # Styles application
├── export/                # Fichiers Excel exportés
├── input/                 # Fichiers à importer
├── vendor/                # Dépendances Composer
├── .htaccess              # Configuration Apache
├── composer.json          # Dépendances PHP
├── index.php              # Page principale
└── micromarket.sql        # Structure base de données
```

## Installation

### Prérequis

- WAMP/LAMP/MAMP installé
- PHP >= 8.1
- MySQL >= 5.7
- Composer installé

### Étapes d'Installation

1. **Cloner le dépôt**
   ```bash
   git clone [URL_DU_DEPOT]
   cd MDM
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Créer la base de données**
   - Importer `micromarket.sql` dans phpMyAdmin ou en ligne de commande :
   ```bash
   mysql -u root -p < micromarket.sql
   ```

4. **Configurer la connexion**
   - Éditer `api/database.php` avec vos identifiants :
   ```php
   private $host = "localhost";
   private $db_name = "micromarket";
   private $username = "root";
   private $password = "root";
   ```

5. **Créer les dossiers nécessaires**
   ```bash
   mkdir -p archives commandes export input
   ```

6. **Configurer Apache**
   - S'assurer que `mod_rewrite` est activé
   - Le fichier `.htaccess` gère les URL propres

7. **Placer les images**
   - Copier les images produits dans `assets/`

## Configuration

### Chemins Système

Les chemins sont définis en dur dans les fichiers. Adapter selon votre environnement :

**Dans `api/import.php`** :
```php
$inputFileName = "C:/wamp64/www/MDM/input/micromarket.xlsx";
$assetDir = "C:/wamp64/www/MDM/assets/";
$archiveDir = "C:/wamp64/www/MDM/archives";
```

**Dans `api/export.php`** :
```php
$exportDir = "C:/wamp64/www/MDM/export";
$commandesDir = "C:/wamp64/www/MDM/commandes";
```

### Format Fichier Import

Le fichier Excel d'import doit respecter cette structure :

| Colonne | Contenu | Format |
|---------|---------|--------|
| B | Code produit | Texte (ex: TVF-01) |
| C | Description | Texte |
| D | Prix | Nombre (centimes) |
| E | ID Catégorie | Nombre |
| F | ID Statut | Nombre |
| G | Nom fournisseur | Texte |
| H | Date achat | Date |
| I | Date péremption | Date |
| J | Nom fichier image | Texte (ex: TVF-01.jpg) |

## Utilisation

### Interface Web

Accéder à `http://localhost/MDM/`

**Fonctionnalités principales** :
- Visualisation tableau produits
- Filtres par catégorie, statut, recherche
- Modification statut produit
- Suppression produit
- Import fichier XLSX
- Export Excel et génération PDF

### API REST

**Base URL** : `http://localhost/MDM/api/product.php`

#### Récupérer tous les produits
```http
GET /api/product
```

#### Récupérer un produit
```http
GET /api/product/{id}
```

#### Ajouter un produit
```http
POST /api/product
Content-Type: application/x-www-form-urlencoded

code=ABC&description=Produit&price=100&category=1&statut=2&supplier=1&purchase=2024-01-01&expire=2025-01-01
```

#### Modifier un produit
```http
PUT /api/product/{id}
Content-Type: application/x-www-form-urlencoded

status=3&image=nouveau-visuel.jpg
```

#### Supprimer un produit
```http
DELETE /api/product/{id}
```

### Processus d'Import

1. Placer le fichier `micromarket.xlsx` dans `input/`
2. Cliquer sur "IMPORTER le fichier XLSX"
3. Le système :
   - Lit le fichier Excel
   - Insère/met à jour les produits
   - Gère les fournisseurs automatiquement
   - Associe les catégories et images
   - Archive le fichier avec horodatage

### Processus d'Export

Cliquer sur "Exporter" pour déclencher :

1. **Export Excel complet** : Tous les produits dans `export/export_YYYY_MM_DD_HH_mm_ss.xls`

2. **Génération bons de commande** : PDF pour chaque produit "Épuisé" dans `commandes/`

3. **Mise à jour statuts** :
   - "Épuisé" → "En cours d'approvisionnement"
   - "En stock" périmés aujourd'hui → "Retiré des rayons"

4. **Export produits retirés** : Fichier `retired_YYYY_MM_DD_HH_mm_ss.xls`

## Structure Base de Données

### Tables Principales

**`produits`** : Informations produits
- `id_product` (PK)
- `code` (UNIQUE)
- `description`
- `price` (centimes)
- `category_id` (FK)
- `statut_id` (FK)
- `supplier_id` (FK)
- `purchase_date`
- `expiration_date`

**`category`** : Catégories produits
- `id` (PK)
- `name`

**`statut`** : États produits
- `id` (PK)
- `name` (1: En cours d'approvisionnement, 2: En stock, 3: Épuisé, 4: Retiré des rayons)

**`suppliers`** : Fournisseurs
- `id` (PK)
- `name`
- `address`

**`assets`** : Fichiers images
- `id` (PK)
- `file_path`
- `file_name`

### Tables de Liaison

**`category_product`** : N-N entre produits et catégories  
**`assets_product`** : N-N entre produits et images (avec `is_primary`)

## Dépannage

### Problèmes Courants

**Erreur connexion base de données**
- Vérifier identifiants dans `api/database.php`
- S'assurer que MySQL est démarré

**Import échoue**
- Vérifier chemin fichier dans `api/import.php`
- Contrôler format Excel (colonnes B à J)
- Vérifier permissions dossiers

**Images non affichées**
- Vérifier présence fichiers dans `assets/`
- Contrôler noms fichiers (sensible à la casse)
- Vérifier chemins dans base de données

**Export PDF échoue**
- Vérifier installation TCPDF via Composer
- Contrôler permissions dossier `commandes/`

**Règles mod_rewrite inactives**
- Activer mod_rewrite dans Apache
- Vérifier `AllowOverride All` dans configuration Apache

## Sécurité

⚠️ **Points d'attention** :

- Changer les identifiants base de données par défaut
- Restreindre accès dossiers `api/`, `vendor/`, `archives/`
- Valider/échapper entrées utilisateur
- Implémenter authentification
- Utiliser HTTPS en production
- Gérer permissions fichiers (pas 777)

## Améliorations Futures

- [ ] Authentification utilisateurs
- [ ] Gestion multi-langues
- [ ] Upload images via interface
- [ ] Historique modifications
- [ ] Notifications email automatiques
- [ ] Dashboard statistiques
- [ ] Export formats multiples (CSV, JSON)
- [ ] API documentation Swagger/OpenAPI
- [ ] Tests unitaires
- [ ] Version responsive mobile

## Contribuer

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créer une branche (`git checkout -b feature/amelioration`)
3. Commit les changements (`git commit -m 'Ajout fonctionnalité'`)
4. Push vers la branche (`git push origin feature/amelioration`)
5. Ouvrir une Pull Request

## Auteur

Projet développé pour la gestion d'un micro-marché

## Licence

À définir

---

**Version** : 1.0  
**Date** : Avril 2025