<?php
// api/client/update_profil_ajax.php
// Endpoint AJAX pour la modification du profil (Phase 3)
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

require_once __DIR__ . '/../includes/config.php'; // ton fichier de connexion PDO

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Validation basique côté serveur
$nom     = trim($data['nom'] ?? '');
$prenom  = trim($data['prenom'] ?? '');
$tel     = trim($data['tel'] ?? '');
$adresse = trim($data['adresse'] ?? '');

if (strlen($nom) < 2 || strlen($prenom) < 2) {
    echo json_encode(['success' => false, 'message' => 'Nom ou prénom trop court.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE Utilisateurs SET nom = ?, prenom = ?, tel = ?, adresse = ? WHERE id_user = ?"
    );
    $stmt->execute([$nom, $prenom, $tel, $adresse, $user_id]);

    $_SESSION['user_name'] = $nom; // Mise à jour de la session

    echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès !']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données.']);
}