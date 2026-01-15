<?php
// Désactiver l'affichage des erreurs en production pour ne pas révéler d'informations sensibles
ini_set('display_errors', 0); 

$host = '192.168.10.5';         // Votre hôte (souvent localhost)
$db   = 'fonivaclist'; // Le nom de votre base de données
$user = 'root';  // L'utilisateur ayant les privilèges
$pass = '';  // Le mot de passe (À NE JAMAIS exposer au frontend)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Afficher les erreurs PDO
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Les résultats sont retournés en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // IMPORTANT : Utiliser les prepared statements natives de MySQL
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Loguer l'erreur dans un fichier dédié
     $logFile = __DIR__ . '/../logs/db_errors.log';
     $timestamp = date('Y-m-d H:i:s');
     $errMsg = "[$timestamp] DB Connection Error: " . $e->getMessage() . PHP_EOL;
     file_put_contents($logFile, $errMsg, FILE_APPEND);

     // Renvoyer une réponse JSON structurée pour le frontend
     http_response_code(500);
     die(json_encode([
        "error" => "Erreur de connexion à la base de données.",
        "is_db_error" => true // Flag pour redirection frontend
     ]));
}
// La variable $pdo est maintenant notre objet de connexion PDO sécurisé.