<?php
// api/includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrateur';
}

function logoutUser() {
    $_SESSION = array();
    session_destroy();
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function registerUser($userData) {
    global $pdo;
    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$userData['email']]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Email déjà utilisé
        }

        // Insérer le nouvel utilisateur (On ignore le 'username' car il n'est pas dans la table SQL)
        $stmt = $pdo->prepare("INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, tel, adresse) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userData['nom'],
            $userData['prenom'],
            $userData['email'],
            password_hash($userData['password'], PASSWORD_DEFAULT),
            'Client', // Rôle par défaut
            $userData['telephone'] ?? '',
            $userData['adresse'] ?? ''
        ]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE id_user = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}
?>