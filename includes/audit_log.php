<?php
/**
 * audit_log.php - Enregistrement d'audit (Version BDD)
 */

/**
 * Enregistrer une requête dans la table audit_logs
 */
function logAudit($username, $query_id, $params, $rowCount = 0, $status = 'success', $error_msg = '') {
    // Utiliser la connexion d'audit (locale)
    global $pdoAudit;
    if (!isset($pdoAudit)) {
        require_once __DIR__ . '/db_audit.php';
    }

    try {
        $timestamp = date('Y-m-d H:i:s');
        $remote_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Extract table name from params if available
        $table_name = null;
        if (is_array($params) && isset($params['table'])) {
            $table_name = $params['table'];
        }

        $sql = "INSERT INTO audit_logs (timestamp, user, query_id, table_name, params, row_count, status, error_msg, remote_ip)
                VALUES (:timestamp, :user, :query_id, :table_name, :params, :row_count, :status, :error_msg, :remote_ip)";
        
        $stmt = $pdoAudit->prepare($sql);
        $stmt->execute([
            ':timestamp'  => $timestamp,
            ':user'       => $username,
            ':query_id'   => $query_id,
            ':table_name' => $table_name,
            ':params'     => json_encode($params),
            ':row_count'  => $rowCount,
            ':status'     => $status,
            ':error_msg'  => $error_msg,
            ':remote_ip'  => $remote_ip
        ]);

    } catch (PDOException $e) {
        // En cas d'erreur BDD, on fallback sur un fichier pour ne pas perdre l'info
        $log_line = date('Y-m-d H:i:s') . " [DB_LOG_ERROR] " . $e->getMessage() . PHP_EOL;
        error_log($log_line, 3, __DIR__ . '/../logs/db_errors.log');
    }
}

/**
 * Lire les logs depuis la BDD
 */
function getAuditLogs($limit = 100) {
    global $pdoAudit;
    if (!isset($pdoAudit)) {
        require_once __DIR__ . '/db_audit.php';
    }

    try {
        $stmt = $pdoAudit->prepare("SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Supprimer les logs antérieurs à une date donnée
 */
function purgeAuditLogs($beforeDate) {
    global $pdoAudit;
    if (!isset($pdoAudit)) {
        require_once __DIR__ . '/db_audit.php';
    }

    try {
        $stmt = $pdoAudit->prepare("DELETE FROM audit_logs WHERE timestamp < :date");
        $stmt->execute([':date' => $beforeDate]);
        return $stmt->rowCount(); // Retourne le nombre de logs supprimés
    } catch (PDOException $e) {
        return false;
    }
}
?>
