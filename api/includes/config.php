<?php
/**
 * Fichier de configuration principale - Projet Yumland (Phase 2)
 * Contient les paramètres globaux, la sécurité et la connexion à la base de données SQL
 */

// Configuration de l'application
define('APP_NAME', 'Le Grand Miam');
define('APP_VERSION', '3.0');
define('DEBUG_MODE', false); // Mettre à false en production

// ---------------------------------------------------------
// 1. SÉCURITÉ ET SESSIONS
// ------------------------------------// Configuration sécurisée des sessions (À mettre AVANT le session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}
// ---------------------------------------------------------
// 2. CONNEXION À LA BASE DE DONNÉES (MYSQL)
// ---------------------------------------------------------

// Configuration MySQL (à adapter selon votre environnement WAMP/XAMPP)
define('DB_HOST', 'yumlandbase-yumland.l.aivencloud.com');
define('DB_PORT', '25645'); // <--- TRÈS IMPORTANT sur Aiven
define('DB_NAME', 'yumlandbase'); // Nom de la base de données
define('DB_USER', 'avnadmin');      // Nouvel utilisateur dédié
define('DB_PASS', 'AVNS_PH3P24uM4D2Vg9YHMvZ'); // Nouveau mot de passe (plus sécurisé)

define('DB_SSL_CA', __DIR__ . '/ca.pem');

try {
    // Création de l'objet PDO pour dialoguer avec MySQL
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    
    // Configuration : déclencher une exception (erreur) si une requête SQL échoue
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configuration : récupérer les données SQL sous forme de tableau associatif
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur critique de connexion à la base de données : " . $e->getMessage());
}

// ---------------------------------------------------------
// 3. FONCTIONS UTILITAIRES ET AUTHENTIFICATION
// ---------------------------------------------------------

function debug($var) {
    if (DEBUG_MODE) {
        echo '<pre style="background:#eee; padding:10px; border:1px solid #ccc;">';
        print_r($var);
        echo '</pre>';
    }
}



function hasRole($role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['user_role'] === $role;
}

// ---------------------------------------------------------
// 4. PROTECTION CSRF (Phase 4)
// ---------------------------------------------------------

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('Erreur de sécurité : Validation du token CSRF a échoué.');
    }
    return true;
}
?>