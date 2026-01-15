<?php
// api_users.php - Gestion des utilisateurs (CRUD)
require 'includes/auth.php';

// Sécurité : Seul l'admin peut gérer les users
if (!isAuthenticated() || getCurrentRole() !== 'admin') {
    http_response_code(403);
    die(json_encode(["error" => "Access denied. Admin rights required."]));
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getAuthPdo();

if (!$pdo) {
    http_response_code(500);
    die(json_encode(["error" => "Auth DB Connection Failed."]));
}

// -----------------------------------------------------------------------------
// GET : Lister les utilisateurs
// -----------------------------------------------------------------------------
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    echo json_encode(["users" => $users]);
    exit;
}

// -----------------------------------------------------------------------------
// POST : Ajouter un utilisateur
// -----------------------------------------------------------------------------
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    $role = trim($input['role'] ?? 'user');

    // Validation
    if (empty($username) || empty($password)) {
        http_response_code(400);
        die(json_encode(["error" => "Username and Password are required."]));
    }
    
    if (strlen($password) < 6) {
        http_response_code(400);
        die(json_encode(["error" => "Password must be at least 6 characters."]));
    }

    // Check doublon
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    if ($stmt->fetch()) {
        http_response_code(409); // Conflict
        die(json_encode(["error" => "Username already exists."]));
    }

    // Création
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:u, :p, :r)");
    
    try {
        $stmt->execute([':u' => $username, ':p' => $hash, ':r' => $role]);
        echo json_encode(["success" => true, "message" => "User created successfully."]);
    } catch (Exception $e) {
        http_response_code(500);
        die(json_encode(["error" => "DB Error: " . $e->getMessage()]));
    }
    exit;
}

// -----------------------------------------------------------------------------
// DELETE : Supprimer un utilisateur
// -----------------------------------------------------------------------------
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        die(json_encode(["error" => "ID required."]));
    }

    // Empêcher de se supprimer soi-même (optionnel mais recommandé)
    // On pourrait vérifier si l'user supprimé == $_SESSION['user'], mais l'ID est plus sûr.
    // Pour simplifier, on autorise tout suppression sauf si l'admin n'a pas son propre ID sous la main pour vérifier.
    
    // Check si l'user existe
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $userToDelete = $stmt->fetch();
    
    if (!$userToDelete) {
        http_response_code(404);
        die(json_encode(["error" => "User not found."]));
    }

    if ($userToDelete['username'] === getCurrentUser()) {
         http_response_code(400);
         die(json_encode(["error" => "You cannot delete your own account."]));
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    echo json_encode(["success" => true, "message" => "User deleted."]);
    exit;
}

// Méthode non supportée
http_response_code(405);
die(json_encode(["error" => "Method not allowed"]));
?>
