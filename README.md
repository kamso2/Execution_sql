# SQL Executor (Admin Panel)

**Version 2.0** - Interface Admin Sécurisée pour l'exécution de requêtes SQL et l'audit.

## 🚀 Fonctionnalités Principales

*   **Exécution de Requêtes** : Interface simple pour lancer des scripts SQL prédéfinis sur la base de production.
*   **Audit Complet** : Tous les actions sont loguées dans une base de données locale dédiée (`audit_logs`) avec IP, utilisateur, et détail de la requête.
*   **Double Architecture BDD** :
    *   **Remote** : Exécution sur serveur distant (`192.168.10.5`).
    *   **Local** : Stockage des logs sur `localhost`.
*   **Gestion Utilisateurs** : Système de rôles (Admin/User) avec contrôle d'accès strict.
*   **Interface Moderne** : Sidebar de navigation, Dark Mode, Tableaux réactifs.

## 🛠️ Installation

### 1. Configuration Base de Données
Le projet utilise deux connexions distinctes.

**A. Base Distante (Production)**
Éditez `includes/db_config.php` :
```php
$host = '192.168.10.5';
$db   = 'fonivaclist';
$user = '...';
$pass = '...';
```

**B. Base Locale (Audit)**
Éditez `includes/db_audit.php` :
```php
$host = '127.0.0.1';
$db   = 'audit_db';
// Credentials locaux (WAMP défaut : root / vide)
```

### 2. Initialisation des Logs
Lancez le script d'installation pour créer la base locale et la table de logs :
```bash
php setup_local_audit.php
```

### 3. Comptes par défaut
*   **Login** : `admin`
*   **Password** : `admin123` (À CHANGER IMPÉRATIVEMENT ! via `auth.php` ou la base `sql_app_auth`)

## 📂 Structure du Projet

*   `index.html` : **Dashboard Principal**. Configuration et lancement des requêtes.
*   `audit_view.html` : **Journal d'Audit**. Visualisation et purge des logs.
*   `admin_users.html` : **Gestion Comptes**. Création/Suppression d'utilisateurs.
*   `includes/` :fichiers de configuration PHP (`db_config`, `query_config`, `auth`).

## 🔒 Sécurité
*   Protection CSRF sur tous les formulaires.
*   Whitelisting strict des tables et colonnes SQL.
*   Isolation des logs d'audit.
