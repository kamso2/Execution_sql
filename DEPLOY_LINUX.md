# 🐧 Guide de Déploiement : Linux (Ubuntu / Debian / CentOS)

Ce guide explique comment installer l'application **SQL Executor** sur un serveur Linux propre (Pile LAMP).

## 1. Installation des Dépendances
Assurez-vous d'avoir Apache, PHP et MySQL installés :
```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql mysql-server -y
```

## 2. Déploiement du Code
1. Copiez les fichiers dans `/var/www/html/Execution_sql`.
2. **IMPORTANT : Permissions**
   Linux nécessite des droits d'écriture explicites pour les logs :
   ```bash
   sudo chown -R www-data:www-data /var/www/html/Execution_sql
   sudo chmod -R 755 /var/www/html/Execution_sql
   # Donner les droits d'écriture aux logs
   sudo chmod -R 777 /var/www/html/Execution_sql/logs
   sudo chmod -R 777 /var/www/html/Execution_sql/includes/logs
   ```

## 3. Configuration MySQL
1. Connectez-vous à MySQL : `sudo mysql -u root -p`
2. Créez les bases de données nécessaires.
3. Importez vos données.
4. Mettez à jour `includes/db_config.php` avec les identifiants Linux.

## 4. Sensibilité à la casse (Case Sensitivity)
Contrairement à Windows, Linux traite `Auth.php` et `auth.php` comme deux fichiers différents.
- **Toutes les inclusions dans ce code sont en minuscules**.
- **Veillez à ne pas renommer les fichiers avec des majuscules** lors du transfert.

## 5. Configuration Apache (VirtualHost)
Pour une meilleure sécurité, créez un fichier de conf dédié `/etc/apache2/sites-available/sql-executor.conf` :
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/Execution_sql
    <Directory /var/www/html/Execution_sql>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Activez le site : `sudo a2ensite sql-executor.conf && sudo systemctl restart apache2`

---
**Logs d'audit** : Les logs se trouvent dans `/var/www/html/Execution_sql/logs/audit.log`. Vérifiez qu'ils se remplissent bien après votre première connexion.
