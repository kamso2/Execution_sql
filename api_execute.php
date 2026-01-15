<?php
// Augmenter la mémoire pour les grosses requêtes
ini_set('memory_limit', '512M');
// 0. AUTHENTIFICATION ET AUDIT
// ---------------------------------------------------------
require 'includes/auth.php';
require 'includes/audit_log.php';

// Vérifier que l'utilisateur est authentifié
if (!isAuthenticated()) {
    header('Content-Type: application/json');
    http_response_code(401);
    die(json_encode(["error" => "Non authentifié. Veuillez vous connecter."]));
}

$current_user = getCurrentUser();

// 1. CONFIGURATION ET CONNEXION
// ---------------------------------------------------------
require 'includes/db_config.php'; // Votre fichier de connexion $pdo
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Utile pour le développement
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Répondre aux requêtes CORS préflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Origin: *');
    // Réponse courte pour debug
    http_response_code(200);
    echo json_encode(["ok" => true, "method" => "OPTIONS"]);
    exit;
}

// Vérification de la méthode
// Vérification de la méthode
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    // Loggage pour diagnostic
    $remote = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    error_log("api_execute.php: méthode reçue='$method' depuis $remote");

    http_response_code(405);
    // Renvoyer une réponse lisible pour débogage (peut être retirée en production)
    die(json_encode(["error" => "Méthode non autorisée. Utilisez POST.", "method_received" => $method]));
}

// Vérification CSRF
$headers = getAllHeaders();
$csrf_token = $headers['X-CSRF-Token'] ?? '';
if (!verifyCsrfToken($csrf_token)) {
    http_response_code(403);
    die(json_encode(["error" => "Échec de la validation CSRF."]));
}

// Lecture des données JSON envoyées par le JS
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Log minimal pour diagnostic : méthode, IP, headers, body
$method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
$remote = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (function_exists('getallheaders')) {
    $hdrs = getallheaders();
} else {
    $hdrs = [];
}
error_log("api_execute.php DEBUG: method={$method} from {$remote} headers=" . json_encode($hdrs) . " body=" . substr($input, 0, 1000));

if (!$data) {
    http_response_code(400);
    die(json_encode(["error" => "Données JSON invalides."]));
}

$query_id = $data['query_id'] ?? null;

// 2. DÉFINITION DES REQUÊTES (DOIT MATCHER LE FRONTEND)
// ---------------------------------------------------------
require 'includes/query_config.php';


// 3. TRAITEMENT DE SÉCURITÉ
// ---------------------------------------------------------

if (!array_key_exists($query_id, $queries)) {
    http_response_code(404);
    die(json_encode(["error" => "Requête inconnue."]));
}

// Vérifier les droits d'accès
$table_requested = $data['table'] ?? null;
if (!hasAccess($query_id, $table_requested)) {
    logAudit($current_user, $query_id, $data, 0, 'error', 'Accès refusé à la requête ou table');
    http_response_code(403);
    die(json_encode(["error" => "Accès refusé à cette requête ou table."]));
}

$query_config = $queries[$query_id];

// VALIDATION DES VALEURS AUTORISÉES
if (isset($query_config['allowed_values'])) {
    $allowed = $query_config['allowed_values'];
    
    // Vérifier 'value' (paramètre de filtre WHERE)
    if (isset($data['value']) && !in_array($data['value'], $allowed)) {
        http_response_code(400);
        die(json_encode(["error" => "Valeur de filtre non autorisée : '" . $data['value'] . "'"]));
    } else if (isset($data['params']['value']) && !in_array($data['params']['value'], $allowed)) {
         http_response_code(400);
         die(json_encode(["error" => "Valeur de filtre non autorisée : '" . $data['params']['value'] . "'"]));
    }

    // Vérifier 'new_status' (nouveau statut)
    if (isset($data['new_status']) && !in_array($data['new_status'], $allowed)) {
        http_response_code(400);
        die(json_encode(["error" => "Nouveau statut non autorisé : '" . $data['new_status'] . "'"]));
    }
}

$sql = $query_config['template'];
$execution_params = [];

require 'includes/query_builder.php';

try {
    $qb_result = buildSqlQuery($query_config, $data);
    $sql = $qb_result['sql'];
    $execution_params = $qb_result['params'];



    // Pour l'affichage, on garde une limite par défaut si pas de filtres, OU on force une limite de sécurité.
    // L'ancien code disait : "Si pas de filtres, LIMIT 100".
    // buildSqlQuery ne gère pas le LIMIT car api_export n'en veut pas.
    // Ajoutons le LIMIT ici.
    
    // Si aucun WHERE n'a été ajouté par buildSqlQuery? C'est dur à savoir sans inspecter le SQL retourné ou modifier return.
    // Simplification: On applique LIMIT 1000 pour la vue écran par sécurité.
    // L'ancien code faisait "LIMIT 100" uniquement si empty($where_clauses).
    // On peut faire mieux: Toujours LIMITer l'affichage écran pour pas tuer le navigateur.
    if (!strpos($sql, 'LIMIT')) {
       $sql .= " LIMIT 100000"; 
    }

} catch (Exception $e) {
    http_response_code(400);
    die(json_encode(["error" => $e->getMessage()]));
}

// Nettoyage final (plus besoin de regex complexes)

// 4. EXÉCUTION
// ---------------------------------------------------------
try {
    // On prépare la requête SQL finale (qui a maintenant les bonnes tables mais des :param)
    $stmt = $pdo->prepare($sql);
    
    // On exécute en injectant les valeurs utilisateurs
    // On exécute en injectant les valeurs utilisateurs
    $stmt->execute($execution_params);
    
    // Détection du type de requête pour le retour
    $is_select = preg_match('/^\s*SELECT/i', $sql);

    if ($is_select) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rowCount = count($results);
    } else {
        // Pour UPDATE, DELETE, INSERT
        $rowCount = $stmt->rowCount();
        $results = []; // Pas de résultats pour un UPDATE
    }
    
    // Audit : succès
    logAudit($current_user, $query_id, $data, $rowCount, 'success', '');

    echo json_encode([
        "success" => true,
        "count" => $rowCount,
        "results" => $results
    ]);

} catch (PDOException $e) {
    // Log interne, renvoyer un message générique au client
    error_log($e->getMessage());
    
    // Audit : erreur
    logAudit($current_user, $query_id, $data, 0, 'error', $e->getMessage());
    
    http_response_code(500);
    die(json_encode(["error" => "Erreur interne côté serveur."]));
}
?>