<?php
// api_get_logs.php - Récupérer les logs d'audit (JSON)
require 'includes/auth.php';
require 'includes/audit_log.php';

// Sécurité : Seul l'admin devrait voir les logs (pour l'instant, on laisse ouvert à tout utilisateur connecté)
// Sécurité : Seul l'admin devrait voir les logs
// getCurrentRole() est défini dans la nouvelle version de auth.php
if (!isAuthenticated() || getCurrentRole() !== 'admin') {
    http_response_code(403);
    die(json_encode(["error" => "Access denied. Admin rights required."]));
}

header('Content-Type: application/json');

// Récupérer les logs (limit 100 par défaut)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$logs = getAuditLogs($limit);

// Inverser pour avoir les plus récents en premier
$logs = array_reverse($logs);

echo json_encode(["logs" => $logs]);
?>
