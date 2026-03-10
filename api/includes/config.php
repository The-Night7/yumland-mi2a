<?php
/**
 * Fichier de configuration principale
 * Contient les constantes et paramètres globaux de l'application
 */

// Chemins des fichiers de données
define('DATA_PATH', __DIR__ . '/../data/');
define('USERS_FILE', DATA_PATH . 'users.json');
define('PLATS_FILE', DATA_PATH . 'plats.json');
define('MENUS_FILE', DATA_PATH . 'menus.json');
define('COMMANDES_FILE', DATA_PATH . 'commandes.json');

// Configuration de l'application
define('APP_NAME', 'Le Grand Miam');
define('APP_VERSION', '2.0');
define('DEBUG_MODE', true); // Mettre à false en production

// Configuration de session
ini_set('session.cookie_httponly', 1); // Protection contre les attaques XSS
ini_set('session.use_only_cookies', 1); // Forcer l'utilisation des cookies
session_start();

// Fonction pour afficher les erreurs en mode debug
function debug($var) {
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

// Fonction pour rediriger l'utilisateur
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier le rôle de l'utilisateur
function hasRole($role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['user_role'] === $role;
}

// Fonction pour charger des données depuis un fichier JSON
function loadData($file) {
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

// Fonction pour sauvegarder des données dans un fichier JSON
function saveData($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($file, $json);
}

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier un token CSRF
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    return true;
}