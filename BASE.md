# Guide de Déploiement - Configuration Base de Données

## Vue d'Ensemble

Ce guide détaille **toutes les procédures** pour déployer l'application Execution_sql dans un nouvel environnement avec des bases de données différentes.

L'application utilise **3 bases de données distinctes** :
1. **Base principale** (`fonivaclist`) - Données métier
2. **Base d'authentification** (`sql_app_auth`) - Comptes utilisateurs
3. **Base d'audit** (`audit_db`) - Journaux d'audit

---

## 📋 Checklist Rapide

- [ ] Configurer la base de données principale
- [ ] Configurer la base d'authentification
- [ ] Configurer la base d'audit
- [ ] Exécuter les scripts d'initialisation
- [ ] Vérifier les connexions
- [ ] Tester l'application

---

## 🔧 Étape 1 : Configuration Base de Données Principale

### Fichier à Modifier

**[includes/db_config.php](file:///c:/wamp64/www/Execution_sql/includes/db_config.php)**

### Lignes à Modifier

| Ligne | Paramètre | Description | Exemple |
|-------|-----------|-------------|---------|
| **5** | `$host` | Adresse du serveur MySQL | `'192.168.10.5'` ou `'localhost'` |
| **6** | `$db` | Nom de la base de données | `'fonivaclist'` |
| **7** | `$user` | Utilisateur MySQL | `'root'` |
| **8** | `$pass` | Mot de passe MySQL | `'votre_mot_de_passe'` |

### Exemple de Configuration

```php
// AVANT (Configuration actuelle)
$host = '192.168.10.5';
$db   = 'fonivaclist';
$user = 'root';
$pass = '';

// APRÈS (Nouvel environnement)
$host = 'db.monentreprise.com';  // Ligne 5
$db   = 'production_data';        // Ligne 6
$user = 'app_user';               // Ligne 7
$pass = 'M0tD3P@ss3S3cur1s3';    // Ligne 8
```

> [!WARNING]
> **Sécurité** : Ne jamais commiter le mot de passe dans Git. Utilisez des variables d'environnement en production.

---

## 🔐 Étape 2 : Configuration Base d'Authentification

### Fichier à Modifier

**[includes/auth.php](file:///c:/wamp64/www/Execution_sql/includes/auth.php)**

### Lignes à Modifier

| Ligne | Constante | Description | Exemple |
|-------|-----------|-------------|---------|
| **10** | `AUTH_DB_HOST` | Serveur de la base auth | `'localhost'` |
| **11** | `AUTH_DB_NAME` | Nom de la base auth | `'sql_app_auth'` |
| **12** | `AUTH_DB_USER` | Utilisateur MySQL | `'root'` |
| **13** | `AUTH_DB_PASS` | Mot de passe MySQL | `''` |

### Exemple de Configuration

```php
// AVANT
define('AUTH_DB_HOST', 'localhost');
define('AUTH_DB_NAME', 'sql_app_auth');
define('AUTH_DB_USER', 'root');
define('AUTH_DB_PASS', '');

// APRÈS (Nouvel environnement)
define('AUTH_DB_HOST', 'auth-db.monentreprise.com'); // Ligne 10
define('AUTH_DB_NAME', 'prod_users');                 // Ligne 11
define('AUTH_DB_USER', 'auth_user');                  // Ligne 12
define('AUTH_DB_PASS', 'Auth@2026!Secure');          // Ligne 13
```

> [!NOTE]
> La base d'authentification peut être sur le **même serveur** que la base principale ou sur un **serveur séparé** pour plus de sécurité.

---

## 📊 Étape 3 : Configuration Base d'Audit

### Fichier à Modifier

**[includes/db_audit.php](file:///c:/wamp64/www/Execution_sql/includes/db_audit.php)**

### Lignes à Modifier

| Ligne | Variable | Description | Exemple |
|-------|----------|-------------|---------|
| **4** | `$host` | Serveur de la base audit | `'127.0.0.1'` |
| **5** | `$db` | Nom de la base audit | `'audit_db'` |
| **6** | `$user` | Utilisateur MySQL | `'root'` |
| **7** | `$pass` | Mot de passe MySQL | `''` |

### Exemple de Configuration

```php
// AVANT
$host = '127.0.0.1';
$db   = 'audit_db';
$user = 'root';
$pass = '';

// APRÈS (Nouvel environnement)
$host = 'localhost';              // Ligne 4
$db   = 'prod_audit_logs';        // Ligne 5
$user = 'audit_user';             // Ligne 6
$pass = 'Aud1t@S3cur3!2026';     // Ligne 7
```

> [!TIP]
> Pour des raisons de performance, la base d'audit est généralement sur **localhost** (même serveur que l'application).

---

## 🚀 Étape 4 : Initialisation des Bases de Données

### 4.1 Créer les Bases de Données

Connectez-vous à MySQL et créez les 3 bases :

```sql
-- Base principale (si elle n'existe pas déjà)
CREATE DATABASE IF NOT EXISTS production_data CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Base d'authentification
CREATE DATABASE IF NOT EXISTS prod_users CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Base d'audit
CREATE DATABASE IF NOT EXISTS prod_audit_logs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4.2 Créer les Utilisateurs MySQL (Recommandé)

```sql
-- Utilisateur pour la base principale
CREATE USER 'app_user'@'%' IDENTIFIED BY 'M0tD3P@ss3S3cur1s3';
GRANT SELECT, INSERT, UPDATE, DELETE ON production_data.* TO 'app_user'@'%';

-- Utilisateur pour l'authentification
CREATE USER 'auth_user'@'localhost' IDENTIFIED BY 'Auth@2026!Secure';
GRANT ALL PRIVILEGES ON prod_users.* TO 'auth_user'@'localhost';

-- Utilisateur pour l'audit
CREATE USER 'audit_user'@'localhost' IDENTIFIED BY 'Aud1t@S3cur3!2026';
GRANT ALL PRIVILEGES ON prod_audit_logs.* TO 'audit_user'@'localhost';

FLUSH PRIVILEGES;
```

### 4.3 Initialiser la Table d'Authentification

**Exécuter le script** : `setup_auth_table.php` (à créer si nécessaire)

Ou manuellement :

```sql
USE prod_users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Créer un compte admin par défaut
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Mot de passe : "password" (À CHANGER IMMÉDIATEMENT)
```

### 4.4 Initialiser la Table d'Audit

**Exécuter le script** : [setup_audit_table.php](file:///c:/wamp64/www/Execution_sql/setup_audit_table.php)

```bash
# Depuis le navigateur
http://votre-domaine.com/setup_audit_table.php

# Ou en ligne de commande
php setup_audit_table.php
```

---

## ✅ Étape 5 : Vérification

### 5.1 Vérifier les Connexions

**Exécuter** : [verify_audit_db.php](file:///c:/wamp64/www/Execution_sql/verify_audit_db.php)

```bash
php verify_audit_db.php
```

**Résultat attendu** :
```
✓ Connexion à la base d'audit réussie
✓ Table audit_logs existe
```

### 5.2 Tester l'Application

1. **Connexion** : Accédez à `login.html`
   - Utilisateur : `admin`
   - Mot de passe : `password` (à changer)

2. **Test de requête** : Exécutez une requête simple
   - Sélectionnez "NOMBRE DE FICHES"
   - Choisissez une table
   - Cliquez sur "Exécuter"

3. **Vérifier les logs** : Accédez à `audit_view.html`
   - Vérifiez que la requête a été enregistrée

---

## 📝 Résumé des Fichiers à Modifier

| Fichier | Lignes | Paramètres |
|---------|--------|------------|
| **includes/db_config.php** | 5-8 | `$host`, `$db`, `$user`, `$pass` |
| **includes/auth.php** | 10-13 | `AUTH_DB_HOST`, `AUTH_DB_NAME`, `AUTH_DB_USER`, `AUTH_DB_PASS` |
| **includes/db_audit.php** | 4-7 | `$host`, `$db`, `$user`, `$pass` |

---

## 🔒 Bonnes Pratiques de Sécurité

### En Production

1. **Utilisateurs dédiés** : Créez des utilisateurs MySQL spécifiques (pas `root`)
2. **Mots de passe forts** : Utilisez des mots de passe complexes
3. **Privilèges minimaux** : Accordez uniquement les privilèges nécessaires
4. **Variables d'environnement** : Stockez les credentials dans `.env`
5. **SSL/TLS** : Activez les connexions chiffrées MySQL

### Exemple avec Variables d'Environnement

```php
// includes/db_config.php (Version sécurisée)
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'fonivaclist';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
```

Fichier `.env` (à ne JAMAIS commiter) :
```env
DB_HOST=db.monentreprise.com
DB_NAME=production_data
DB_USER=app_user
DB_PASS=M0tD3P@ss3S3cur1s3

AUTH_DB_HOST=auth-db.monentreprise.com
AUTH_DB_NAME=prod_users
AUTH_DB_USER=auth_user
AUTH_DB_PASS=Auth@2026!Secure

AUDIT_DB_HOST=localhost
AUDIT_DB_NAME=prod_audit_logs
AUDIT_DB_USER=audit_user
AUDIT_DB_PASS=Aud1t@S3cur3!2026
```

---

## 🐛 Dépannage

### Erreur : "Connexion à la base de données échouée"

1. Vérifiez les credentials dans les 3 fichiers de configuration
2. Testez la connexion MySQL manuellement :
   ```bash
   mysql -h HOST -u USER -p DATABASE
   ```
3. Vérifiez que l'utilisateur a les privilèges nécessaires

### Erreur : "Table 'audit_logs' doesn't exist"

Exécutez le script d'initialisation :
```bash
php setup_audit_table.php
```

### Erreur : "Access denied for user"

Vérifiez les privilèges de l'utilisateur :
```sql
SHOW GRANTS FOR 'app_user'@'%';
```

---

## 📚 Ressources Complémentaires

- [DEPLOY_WINDOWS.md](file:///c:/wamp64/www/Execution_sql/DEPLOY_WINDOWS.md) - Déploiement sur Windows
- [DEPLOY_LINUX.md](file:///c:/wamp64/www/Execution_sql/DEPLOY_LINUX.md) - Déploiement sur Linux
- [README.md](file:///c:/wamp64/www/Execution_sql/README.md) - Documentation générale
