<?php
/**
 * auth.php - Gestion de l'authentification (Base Locale)
 */

session_start();

// CONFIGURATION AUTHENTIFICATION
// -----------------------------------------------------------------------------
define('AUTH_DB_HOST', 'localhost');
define('AUTH_DB_NAME', 'sql_app_auth');
define('AUTH_DB_USER', 'root');
define('AUTH_DB_PASS', '');

// Définition des permissions par Rôle
define('ROLE_PERMISSIONS', [
    'admin' => [
        'nombre_de_fiches' => [
            'repeteurs', 'retour_echus', 'one_three_months', 'welcome_call',
            'access_evasion_tc_inactifs', 'echus_3_4_mois', 'evasion_actifs',
            'fragile_retour_echus', 'g11_bundle', 'insight_plus', 'netflix_inactif',
            'project_g11', 'reconquete_access_evasion', 'service_plus',
            'ultimate_evasion_plus', 'ultimate_toutcanal_plus', 'upgrade_access_evasion',
            'welcome_g11'
        ],
        'RELANCE' => [
            'repeteurs', 'retour_echus', 'one_three_months', 'welcome_call',
            'access_evasion_tc_inactifs', 'echus_3_4_mois', 'evasion_actifs',
            'fragile_retour_echus', 'g11_bundle', 'insight_plus', 'netflix_inactif',
            'project_g11', 'reconquete_access_evasion', 'service_plus',
            'ultimate_evasion_plus', 'ultimate_toutcanal_plus', 'upgrade_access_evasion',
            'welcome_g11'
        ],
        'AJOUT_DATE' => [
            'repeteurs', 'retour_echus', 'one_three_months', 'welcome_call',
            'access_evasion_tc_inactifs', 'echus_3_4_mois', 'evasion_actifs',
            'fragile_retour_echus', 'g11_bundle', 'insight_plus', 'netflix_inactif',
            'project_g11', 'reconquete_access_evasion', 'service_plus',
            'ultimate_evasion_plus', 'ultimate_toutcanal_plus',
            'upgrade_access_evasion', 'welcome_g11'
        ],
        'STOP_DATE' => [
            'repeteurs', 'retour_echus', 'one_three_months', 'welcome_call',
            'access_evasion_tc_inactifs', 'echus_3_4_mois', 'evasion_actifs',
            'fragile_retour_echus', 'g11_bundle', 'insight_plus', 'netflix_inactif',
            'project_g11', 'reconquete_access_evasion', 'service_plus',
            'ultimate_evasion_plus', 'ultimate_toutcanal_plus',
            'upgrade_access_evasion', 'welcome_g11'
        ],

    ],
    'user' => [
        'nombre_de_fiches' => [
            'repeteurs', 'retour_echus', 'one_three_months', 'welcome_call',
            'access_evasion_tc_inactifs', 'echus_3_4_mois', 'evasion_actifs',
            'fragile_retour_echus', 'g11_bundle', 'insight_plus', 'netflix_inactif',
            'project_g11', 'reconquete_access_evasion', 'service_plus',
            'ultimate_evasion_plus', 'ultimate_toutcanal_plus', 'upgrade_access_evasion',
            'welcome_g11'
        ],
    ],
]);

/**
 * Connexion à la base d'authentification
 */
function getAuthPdo() {
    $dsn = "mysql:host=" . AUTH_DB_HOST . ";dbname=" . AUTH_DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        return new PDO($dsn, AUTH_DB_USER, AUTH_DB_PASS, $options);
    } catch (PDOException $e) {
        error_log("Auth DB Connection Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Vérifier si l'utilisateur est authentifié avec Timeout de 60 minutes
 */
function isAuthenticated() {
    // Durée d'inactivité max (secondes)
    $timeout_duration = 3600; // 60 minutes

    if (isset($_SESSION['user']) && isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        
        // Vérification du timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            // Session expirée
            session_unset();     
            session_destroy();   
            return false;
        }
        
        // Mise à jour du timestamp d'activité
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

/**
 * Récupérer l'utilisateur actuel
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Récupérer le rôle de l'utilisateur actuel
 */
function getCurrentRole() {
    return $_SESSION['role'] ?? 'user';
}

/**
 * Vérifier les droits d'accès pour une requête
 */
function hasAccess($query_id, $table) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $role = getCurrentRole();
    $perms = ROLE_PERMISSIONS[$role] ?? [];
    
    if (!isset($perms[$query_id])) {
        return false;
    }
    
    return in_array($table, $perms[$query_id]);
}

/**
 * Login
 */
function login($username, $password) {
    $pdo = getAuthPdo();
    if (!$pdo) return false;

    try {
        $stmt = $pdo->prepare("SELECT username, password, role FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            return true;
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
    }

    return false;
}

/**
 * Logout
 */
function logout() {
    session_destroy();
    setcookie('PHPSESSID', '', time() - 3600, '/');
}

/**
 * CSRF
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
