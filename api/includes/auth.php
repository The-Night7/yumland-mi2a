<?php
/**
 * Fonctions liées à l'authentification des utilisateurs
 */
require_once __DIR__ . '/config.php';

/**
 * Authentifie un utilisateur
 * @param string $username Nom d'utilisateur
 * @param string $password Mot de passe (en clair)
 * @return array|bool Données de l'utilisateur si authentifié, false sinon
 */
function authenticateUser($username, $password) {
    $users = loadData(USERS_FILE);
    
    foreach ($users as $user) {
        if ($user['username'] === $username && 
            $user['password'] === md5($password) && 
            $user['status'] === 'active') {
            
            // Ne pas stocker le mot de passe en session
            unset($user['password']);
            
            // Stocker les informations utilisateur en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            
            return $user;
        }
    }
    
    return false;
}

/**
 * Déconnecte l'utilisateur actuel
 */
function logoutUser() {
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Si un cookie de session existe, le détruire
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Détruire la session
    session_destroy();
}

/**
 * Récupère les informations d'un utilisateur par son ID
 * @param int $userId ID de l'utilisateur
 * @return array|null Données de l'utilisateur ou null si non trouvé
 */
function getUserById($userId) {
    $users = loadData(USERS_FILE);
    
    foreach ($users as $user) {
        if ($user['id'] == $userId) {
            // Ne pas renvoyer le mot de passe
            unset($user['password']);
            return $user;
        }
    }
    
    return null;
}

/**
 * Enregistre un nouvel utilisateur
 * @param array $userData Données de l'utilisateur
 * @return bool|int ID de l'utilisateur créé ou false en cas d'échec
 */
function registerUser($userData) {
    $users = loadData(USERS_FILE);
    
    // Vérifier si le nom d'utilisateur existe déjà
    foreach ($users as $user) {
        if ($user['username'] === $userData['username']) {
            return false;
        }
    }
    
    // Générer un nouvel ID
    $newId = 1;
    if (!empty($users)) {
        $lastUser = end($users);
        $newId = $lastUser['id'] + 1;
    }
    
    // Préparer les données du nouvel utilisateur
    $newUser = [
        'id' => $newId,
        'username' => $userData['username'],
        'password' => md5($userData['password']), // En production, utilisez password_hash()
        'email' => $userData['email'],
        'role' => 'client', // Par défaut, tous les nouveaux utilisateurs sont des clients
        'nom' => $userData['nom'],
        'prenom' => $userData['prenom'],
        'adresse' => $userData['adresse'] ?? '',
        'telephone' => $userData['telephone'] ?? '',
        'status' => 'active'
    ];
    
    // Ajouter le nouvel utilisateur
    $users[] = $newUser;
    
    // Sauvegarder les données
    if (saveData(USERS_FILE, $users)) {
        return $newId;
    }
    
    return false;
}