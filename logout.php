<?php
/**
 * logout.php - Gérer la déconnexion
 */

require 'includes/auth.php';

logout();

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
?>
