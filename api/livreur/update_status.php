<?php
// api/livreur/update_status.php
// Endpoint AJAX — Phase 3 : Interface Livreur
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/config.php';

// Sécurité : Seul un Livreur (ou Admin) peut valider la livraison
$role = $_SESSION['user_role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['Livreur', 'Administrateur'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé. Vous devez être livreur.']);
    exit;
}

// Récupération des données envoyées via fetch()
$data = json_decode(file_get_contents('php://input'), true);
$id_commande = (int)($data['id_commande'] ?? 0);
$new_statut = trim($data['new_statut'] ?? '');

// Les statuts que le livreur est en droit d'appliquer
$allowed_statuts = ['Prête', 'En livraison', 'Livrée'];

if ($id_commande <= 0 || !in_array($new_statut, $allowed_statuts)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides ou statut non autorisé.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE Commandes SET statut = ? WHERE id_commande = ?");
    $stmt->execute([$new_statut, $id_commande]);

    echo json_encode([
        'success' => true,
        'new_statut' => $new_statut,
        'message' => "La commande #$id_commande est maintenant $new_statut."
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la base de données.']);
}

// 1. On crée le message de succès
$_SESSION['flash_message'] = "La commande a bien été marquée comme livrée !";
$_SESSION['flash_type'] = "success"; // Permet de gérer la couleur (vert pour succès)

// 2. On redirige vers la page des livraisons
header('Location: livraisons.php');
exit;
?>