<?php
/**
 * Fichier de configuration principale - Projet Yumland (Phase 2)
 * Contient les paramètres globaux, la sécurité et la connexion à la base de données SQL
 */

// Configuration de l'application
define('APP_NAME', 'Le Grand Miam');
define('APP_VERSION', '2.0');
define('DEBUG_MODE', true); // Mettre à false en production

// ---------------------------------------------------------
// 1. SÉCURITÉ ET SESSIONS
// ---------------------------------------------------------
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// ---------------------------------------------------------
// 2. CONNEXION À LA BASE DE DONNÉES (SQLITE)
// ---------------------------------------------------------
// On définit le chemin vers le fichier de base de données SQLite
// (On recule de deux dossiers depuis api/includes/ pour aller dans data/)
define('DB_PATH', __DIR__ . '/../../data/yumland.db');

try {
    // Création de l'objet PDO pour dialoguer avec SQLite
    $pdo = new PDO('sqlite:' . DB_PATH);
    
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

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
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