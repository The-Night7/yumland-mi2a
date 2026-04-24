<?php
/**
 * Fichier de configuration principale - Projet Yumland
 * Gère la connexion hybride (Local / Vercel / Aiven)
 */

// 1. CONFIGURATION DE L'APPLICATION
define('APP_NAME', 'Le Grand Miam');
define('APP_VERSION', '3.0');
define('DEBUG_MODE', true); // Mettez à false une fois que tout fonctionne sur Vercel

// Configuration du fuseau horaire (Heure de Paris)
date_default_timezone_set('Europe/Paris');

// 2. SÉCURITÉ ET SESSIONS
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Penser à décommenter cette ligne lors du passage en HTTPS sur Vercel :
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

// Si on est sur Vercel, on récupère le contenu du certificat depuis les variables d'environnement
$ca_content = getenv('DB_SSL_CA');

if ($ca_content) {
    // Vercel étant en lecture seule, on stocke le certificat SSL de la BDD temporairement dans /tmp
    $ssl_ca = sys_get_temp_dir() . '/aiven_ca.pem';
    
    if (!file_exists($ssl_ca)) {
        file_put_contents($ssl_ca, str_replace('\n', "\n", $ca_content));
    }
}

// 4. CONNEXION À LA BASE DE DONNÉES
try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Activation du SSL pour Aiven / Vercel
    if (file_exists($ssl_ca)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
        // Optionnel : désactive la vérification du nom d'hôte si certificat auto-signé
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; 
    }

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Synchroniser le fuseau horaire de MySQL avec celui de PHP
    $offset = date('P'); // Ex: +01:00 (hiver) ou +02:00 (été)
    $pdo->exec("SET time_zone = '$offset'");

} catch (PDOException $e) {
    // Message d'erreur personnalisé en cas de mise en veille de la BDD Aiven (Plan Gratuit)
    $errorMsg = '<div style="font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; border: 3px solid #D32F2F; border-radius: 8px; background-color: #FDFBF7; color: #2D2D2D; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">';
    $errorMsg .= '<h2 style="color: #D32F2F; margin-top: 0; text-transform: uppercase;">⚠️ Base de données en veille</h2>';
    $errorMsg .= '<p style="font-size: 1.1rem; line-height: 1.5;">Notre application utilise un plan gratuit pour la base de données SQL. Celle-ci se désactive automatiquement après quelques jours d\'inactivité.</p>';
    $errorMsg .= '<p style="font-size: 1.2rem;"><strong>Pas de panique, il faut simplement la réactiver !</strong></p>';
    $errorMsg .= '<p>Veuillez nous envoyer un message pour que nous puissions la relancer immédiatement :</p>';
    $errorMsg .= '<div style="background: white; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #eee; text-align: left; display: inline-block;">';
    $errorMsg .= '<p style="margin: 5px 0;">📞 <strong>Myriam Bensaid</strong> : 06 68 39 92 06</p>';
    $errorMsg .= '<p style="margin: 5px 0;">📞 <strong>Sheryne Ouarghi</strong> : 06 17 67 77 02</p>';
    $errorMsg .= '<p style="margin: 10px 0 0 0; font-size: 0.9em; color: #666; font-style: italic;">(Ou contactez-nous via Teams / Mail de l\'école)</p>';
    $errorMsg .= '</div>';
    
    if (DEBUG_MODE) {
        $errorMsg .= '<hr style="margin-top: 20px; border: 0; border-top: 1px solid #ccc;">';
        $errorMsg .= '<p style="font-size: 0.8rem; color: #666; text-align: left;"><strong>Erreur technique :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    $errorMsg .= '</div>';
    
    die($errorMsg);
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