<?php
/**
 * audit_log.php - Enregistrement d'audit des requêtes exécutées
 */

/**
 * Enregistrer une requête dans le log d'audit
 * @param string $user Utilisateur qui a exécuté la requête
 * @param string $query_id ID de la requête
 * @param array $params Paramètres utilisés
 * @param int $rowCount Nombre de lignes retournées
 * @param string $status 'success' ou 'error'
 * @param string $error_msg Message d'erreur (si status = error)
 */
function logAudit($user, $query_id, $params, $rowCount = 0, $status = 'success', $error_msg = '') {
    $timestamp = date('Y-m-d H:i:s');
    $remote_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Format du log : JSON (facile à parser ultérieurement)
    $log_entry = [
        'timestamp' => $timestamp,
        'user' => $user,
        'query_id' => $query_id,
        'params' => $params,
        'row_count' => $rowCount,
        'status' => $status,
        'error' => $error_msg,
        'remote_ip' => $remote_ip,
    ];
    
    $log_line = json_encode($log_entry) . PHP_EOL;
    
    // Écrire dans un fichier de log (dossier logs/ doit exister et être accessible en écriture)
    $log_file = __DIR__ . '/logs/audit.log';
    
    // Créer le dossier logs s'il n'existe pas
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    error_log($log_line, 3, $log_file);
}

/**
 * Lire les logs d'audit (optionnel, pour un futur dashboard)
 * @param int $limit Nombre de lignes à retourner
 * @return array
 */
function getAuditLogs($limit = 100) {
    $log_file = __DIR__ . '/logs/audit.log';
    
    if (!file_exists($log_file)) {
        return [];
    }
    
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_slice($lines, -$limit); // Dernières $limit lignes
    
    $logs = [];
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $logs[] = $entry;
        }
    }
    
    return $logs;
}
?>
