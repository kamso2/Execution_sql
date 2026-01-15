# 🖥️ Guide de Déploiement : Windows (WAMP / XAMPP)

Ce guide explique comment installer et configurer l'application **SQL Executor** sur un environnement Windows.

## 1. Prérequis
- **WAMP Server** ou **XAMPP** installé.
- PHP version 7.4 ou supérieure.
- MySQL / MariaDB.

## 2. Installation des Fichiers
1. Copiez le dossier `Execution_sql` dans votre répertoire web :
   - Pour WAMP : `C:\wamp64\www\`
   - Pour XAMPP : `C:\xampp\htdocs\`
2. Assurez-vous que le dossier se nomme exactement `Execution_sql`.

## 3. Configuration de la Base de Données
### A. Création de la base d'authentification
1. Ouvrez **phpMyAdmin**.
2. Allez dans l'onglet **Importer**.
3. (Si vous avez un fichier `.sql`) Importez le schéma d'authentification.
4. **Alternativement** : Exécutez le script automatique :
   - Ouvrez votre navigateur sur `http://localhost/Execution_sql/install_auth_db.php`.
   - Cela créera la base `sql_app_auth` et l'utilisateur admin par défaut (`admin / admin123`).

### B. Connexion aux données métiers
Éditez le fichier `includes/db_config.php` :
```php
$host = 'localhost'; // Ou l'IP de votre serveur SQL
$db   = 'votre_base_metier';
$user = 'root';
$pass = ''; // Votre mot de passe MySQL
```

## 4. Réglages PHP (Optionnel mais recommandé)
Pour gérer de gros volumes de données, modifiez votre `php.ini` :
- `memory_limit = 512M`
- `max_execution_time = 300`
- `post_max_size = 50M`

## 5. Accès à l'application
Ouvrez votre navigateur sur : `http://localhost/Execution_sql/`

---
**Note Sécurité** : En production, changez immédiatement le mot de passe du compte `admin` via le menu **Gestion Utilisateurs**.
