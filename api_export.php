<?php
// api_export.php - Export CSV Streaming (Optimisé pour gros volumes)
require 'includes/auth.php';
require 'includes/audit_log.php';

// Vérification authentification
if (!isAuthenticated()) {
    http_response_code(401);
    die("Erreur: Non authentifié.");
}

$current_user = getCurrentUser();

// 1. Configuration & Connexion (similaire à api_execute.php)
require 'includes/db_config.php';
require 'includes/query_config.php';

// 2. Récupération des paramètres (POST form data)
// Contrairement à api_execute (JSON), ici on reçoit du $_POST classique via le formulaire caché
$query_id = $_POST['query_id'] ?? null;

if (!array_key_exists($query_id, $queries)) {
    die("Erreur: Requête inconnue.");
}

// Vérifier les droits
$table_requested = $_POST['table'] ?? null;
if (!hasAccess($query_id, $table_requested)) {
    logAudit($current_user, $query_id, $_POST, 0, 'error', 'Export refusé (droits insuffisants)');
    die("Erreur: Accès refusé.");
}

$query_config = $queries[$query_id];
$sql = $query_config['template'];
$execution_params = [];

require 'includes/query_builder.php';

try {
    // api_export reçoit $_POST, qui est un array plat pour les params de base
    // mais query_builder s'attend à params[value] ou params[start_date] si ça vient du JSON
    // OU à value/start_date direct s'il a été codé pour gérer les deux.
    // Vérifions query_builder.php : 
    // $start_date = $inputs['params']['start_date'] ?? $inputs['start_date'] ?? null;
    // C'est bon, il gère les deux cas !
    
    $qb_result = buildSqlQuery($query_config, $_POST);
    $sql = $qb_result['sql'];
    $execution_params = $qb_result['params'];
    
    // Pas de LIMIT pour l'export.
    
} catch (Exception $e) {
    die("Erreur Export: " . $e->getMessage());
}

// 3. Output Headers pour téléchargement
$filename = "export_" . $query_id . "_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 4. Flux de sortie & BOM Excel
$output = fopen('php://output', 'w');
// BOM UTF-8 pour qu'Excel ouvre le fichier correctement avec les accents
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 5. Exécution & Streaming
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($execution_params);

    // Récupérer les noms de colonnes pour le header CSV
    $firstRow = true;
    $rowCount = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($firstRow) {
            fputcsv($output, array_keys($row), ';'); // Point-virgule pour Excel fr
            $firstRow = false;
        }
        fputcsv($output, $row, ';');
        $rowCount++;
        
        // Vider le buffer PHP périodiquement pour éviter memory limit
        if ($rowCount % 1000 === 0) flush();
    }

    // Audit
    logAudit($current_user, $query_id, $_POST, $rowCount, 'success', 'Export CSV');

} catch (PDOException $e) {
    // Si erreur pendant le stream, c'est compliqué car les headers sont partis.
    // On logue juste côté serveur.
    error_log("Export Error: " . $e->getMessage());
}

fclose($output);
exit;
?>
