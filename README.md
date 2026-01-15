# 🔍 SQL Executor - Suite de Gestion de Données Sécurisée

Une application web moderne et robuste permettant l'exploration de données SQL, l'exécution d'actions de masse (Relance, Reset) et le suivi d'audit.

## ✨ Fonctionnalités Clés

- 🔐 **Gestion d'Accès Avancée** :
    - Authentification basée sur base de données (`MySQL/MariaDB`).
    - Rôles granulaires : **Administrateur** (Actions de masse + Audit + Users) ou **Utilisateur** (Consultation uniquement).
- 🛡️ **Sécurité Renforcée** :
    - Préparation des requêtes SQL via whitelisting strict (Tables et Colonnes).
    - Protection CSRF systématique sur tous les appels API.
    - Journal d'audit complet (Stockage JSON) pour chaque requête effectuée.
- 📊 **Actions & Reporting Dynamiques** :
    - **NOMBRE DE FICHES** : Exploration rapide avec filtres simplifiés.
    - **RELANCE (Admin)** : Mise à jour massive des statuts (ex: NOT_DIALED -> CALLBACK).
    - **AJOUT DATE (Admin)** : Réinitialisation des tentatives d'appel sur une période donnée.
    - **Export CSV Streaming** : Capable de gérer des volumes importants (18k+ lignes) avec une empreinte mémoire réduite.
- 🎨 **Interface Moderne (UI/UX)** :
    - Design premium avec **Mode Sombre** persistant.
    - Système de notifications (Toasts) en bas à droite avec historique de 10 min.
    - Pagination fluide côté client (50 lignes par page).

---

## 🚀 Installation & Configuration

### 1. Prérequis
- Serveur PHP 7.4+ avec extensions `pdo_mysql`.
- Serveur MySQL/MariaDB.

### 2. Configuration BDD
Éditez `includes/db_config.php` pour connecter l'application à votre base métier (ex: `fonivaclist`).
```php
$host = '192.168.10.5';
$db   = 'fonivaclist';
$user = 'root';
$pass = '';
```

### 3. Initialisation de l'Auth
1. Configurez les accès dans `install_auth_db.php`.
2. Exécutez : `php install_auth_db.php`
   *Crée la base `sql_app_auth` (comptes par défaut : `admin/admin123`).*

---

## 📂 Architecture Factorisée

Le projet utilise une architecture **DRY** (Don't Repeat Yourself) pour faciliter la maintenance :

### ⚙️ Centralisation du Mapping
- **`includes/query_config.php`** : Contient la variable `$COMMON_TABLES`. Toute nouvelle table ajoutée ici devient disponible pour tous les rapports compatibles.
- **`script.js`** : Contient `COMMON_TABLE_OPTIONS`. La liste des tables dans l'interface est synchronisée globalement.

### 🛠️ Composants Backend
- `api_execute.php` : Cœur de l'application, gère l'exécution sécurisée.
- `api_export.php` : Moteur d'exportation CSV optimisé.
- `includes/query_builder.php` : Assembleur de requêtes SQL protégeant contre les injections.
- `includes/auth.php` : Gestion des rôles et des jetons CSRF.

---

## � Guide d'Administration

### Ajouter une nouvelle table
Grâce à la factorisation, l'ajout d'une table se fait en seulement deux endroits :
1.  **Backend** : Ajoutez la table dans `$COMMON_TABLES` (dans `includes/query_config.php`).
2.  **Frontend** : Ajoutez-la dans la constante `COMMON_TABLE_OPTIONS` (au début de `script.js`).

### Sécurité Production
- Réglez `ini_set('display_errors', 0)` dans `includes/db_config.php`.
- Changez les mots de passe par défaut via le menu **Utilisateurs**.
- Surveillez régulièrement les logs dans `logs/audit.log`.

---

## 📄 Licence & Support
Développement Janvier 2026. Usage interne restreint.
Support technique : Consultez l'historique d'audit via l'interface dédiée.
Editer par : KONE Abdoul Karim <kamso1805@gmail.com> - Admin systeme ADKONTACT CI
Version : 1.0.0

