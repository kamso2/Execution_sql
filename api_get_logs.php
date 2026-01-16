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

// Initialiser le header JSON
header('Content-Type: application/json');

try {
    // 1. Gestion de la PURGE (DELETE / POST action=purge)
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_GET['action']) && $_GET['action'] === 'purge')) {
        
        // Récupérer la date limite (params URL ou Body)
        $date = $_GET['date'] ?? null;
        if (!$date) {
            $input = json_decode(file_get_contents('php://input'), true);
            $date = $input['date'] ?? null;
        }

        if (!$date) {
            http_response_code(400);
            echo json_encode(["error" => "Date parameter required"]);
            exit;
        }

        $deletedCount = purgeAuditLogs($date);
        
        if ($deletedCount === false) {
            throw new Exception("Error purging logs");
        }

        echo json_encode(["success" => true, "deleted_count" => $deletedCount]);
        exit;
    }

    // 2. Récupération des logs (GET)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $logs = getAuditLogs($limit);

    // Pas besoin d'array_reverse car la requête SQL fait déjà "ORDER BY timestamp DESC"
    echo json_encode(["logs" => $logs]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
