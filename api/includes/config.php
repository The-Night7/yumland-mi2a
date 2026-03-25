<?php
/**
 * Fichier de configuration principale - Projet Yumland
 * Gère la connexion hybride (Local / Vercel / Aiven)
 */

// 1. CONFIGURATION DE L'APPLICATION
define('APP_NAME', 'Le Grand Miam');
define('APP_VERSION', '3.0');
define('DEBUG_MODE', true); // Mettez à false une fois que tout fonctionne sur Vercel

// 2. SÉCURITÉ ET SESSIONS
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Sur Vercel (HTTPS), décommentez la ligne suivante :
    // ini_set('session.cookie_secure', 1); 
    session_start();
}

// 3. RÉCUPÉRATION DES PARAMÈTRES (Priorité aux variables d'environnement Vercel)
$host = getenv('DB_HOST')     ?: 'yumlandbase-yumland.l.aivencloud.com';
$port = getenv('DB_PORT')     ?: '25645';
$db   = getenv('DB_NAME')     ?: 'defaultdb';
$user = getenv('DB_USER')     ?: 'avnadmin';
$pass = getenv('DB_PASSWORD') ?: 'AVNS_PH3P24uM4D2Vg9YHMvZ';

// Chemin vers le certificat SSL (indispensable pour Aiven)
$ssl_ca = __DIR__ . '/ca.pem';

// 4. CONNEXION À LA BASE DE DONNÉES
try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
<<<<<<< HEAD
        // Active le SSL (Obligatoire pour Aiven)
        PDO::MYSQL_ATTR_SSL_CA       => DB_SSL_CA, 
        // Garde la connexion ouverte pour accélérer les chargements (Persistance)
        PDO::ATTR_PERSISTENT         => true,
=======
        PDO::ATTR_EMULATE_PREPARES   => false,
>>>>>>> bd070397593312fe9ea61abe4ac245172998cdc8
    ];

    // Activation du SSL pour Aiven / Vercel
    if (file_exists($ssl_ca)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
        // Optionnel : désactive la vérification du nom d'hôte si certificat auto-signé
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; 
    }

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    if (DEBUG_MODE) {
        die("Erreur critique de connexion : " . $e->getMessage());
    } else {
        die("Erreur de connexion au serveur de données. Veuillez réessayer plus tard.");
    }
}

/**
 * Debug propre
 */
function debug($var) {
    if (DEBUG_MODE) {
        echo '<pre style="background:#f4f4f4; padding:10px; border:1px solid #ccc; font-size:12px;">';
        print_r($var);
        echo '</pre>';
    }
}

/**
 * Sécurité CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        die('Erreur de sécurité CSRF.');
    }
    return true;
}
?>