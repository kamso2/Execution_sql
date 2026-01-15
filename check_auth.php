<?php
/**
 * check_auth.php - Vérifier l'authentification et retourner le statut en JSON
 */

require 'includes/auth.php';

header('Content-Type: application/json');

if (isAuthenticated()) {
    echo json_encode([
        'authenticated' => true,
        'user' => getCurrentUser(),
        'role' => getCurrentRole(),
        'csrf_token' => generateCsrfToken()
    ]);
} else {
    echo json_encode([
        'authenticated' => false,
        'user' => null
    ]);
}
?>
