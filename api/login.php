<?php
// api/login.php

// 1. On appelle notre fichier de configuration (qui contient la connexion $pdo)
require_once __DIR__ . '/includes/config.php';

// On indique que ce fichier va renvoyer du format JSON
header('Content-Type: application/json');

// 2. On vérifie que les données ont bien été envoyées par la page HTML (Méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification de la sécurité CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["success" => false, "message" => "Erreur de sécurité CSRF. Veuillez rafraîchir la page."]);
        exit;
    }

    // On récupère l'email et le mot de passe tapés par l'utilisateur
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // 3. On cherche l'utilisateur dans la base de données
        $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 4. Règle de Sécurité (Phase 4) : On vérifie le mot de passe haché
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            
            // Le mot de passe est bon ! On donne le badge à l'utilisateur (Session)
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['nom'];
            
            // On renvoie une réponse positive au navigateur
            echo json_encode([
                "success" => true, 
                "message" => "Connexion réussie", 
                "role" => $user['role']
            ]);
            
        } else {
            // Mauvais email ou mauvais mot de passe
            echo json_encode(["success" => false, "message" => "Identifiants incorrects."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erreur du serveur."]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
}
?>