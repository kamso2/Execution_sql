<?php
// includes/db_audit.php - Connexion pour les logs (Localhost)

$host = '127.0.0.1'; // Localhost
$db   = 'audit_db';  // Nouvelle base dédiée aux logs
$user = 'root';      // WAMP default
$pass = '';          // WAMP default
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset"; // Sans dbname pour pouvoir créer la base si besoin
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdoAudit = new PDO($dsn, $user, $pass, $options);
    
    // Essayer de sélectionner la base, sinon la connection reste valide mais sans DB sélectionnée
    // (Le script de setup se chargera de la créer)
    $pdoAudit->exec("USE `$db`");
    
} catch (\PDOException $e) {
    // Si la base n'existe pas encore, on ignore l'erreur 'Unknown database' ici
    // pour permettre au script d'installation de tourner.
    // Pour les autres erreurs, on log.
    if (strpos($e->getMessage(), "Unknown database") === false) {
        error_log("Audit DB Connection Error: " . $e->getMessage());
    }
}
?>
