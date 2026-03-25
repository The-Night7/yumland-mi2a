<?php
require_once __DIR__ . '/includes/config.php';

// Force le retour en JSON pour le traitement AJAX
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Protection contre les requêtes falsifiées (CSRF)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["success" => false, "message" => "Erreur de sécurité CSRF. Veuillez rafraîchir la page."]);
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // Recherche de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérification sécurisée du mot de passe
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            
            // Authentification réussie, initialisation de la session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['nom'];
            
            echo json_encode([
                "success" => true, 
                "message" => "Connexion réussie", 
                "role" => $user['role']
            ]);
            
        } else {
            echo json_encode(["success" => false, "message" => "Identifiants incorrects."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erreur du serveur."]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
}
?>