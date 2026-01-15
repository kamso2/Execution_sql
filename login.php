<?php
/**
 * login.php - Traitement de la connexion
 */

require 'includes/auth.php';

// Si déjà connecté, rediriger vers index.html
if (isAuthenticated()) {
    header('Location: index.html');
    exit;
}

// Traitement du formulaire de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        // Succès : rediriger vers index.html
        header('Location: index.html');
        exit;
    } else {
        // Échec : rediriger vers login.html avec message d'erreur
        header('Location: login.html?error=invalid');
        exit;
    }
}

// GET sans POST : afficher le formulaire
header('Location: login.html');
?>
