<?php
// api/restaurateur/update_status.php
// Endpoint AJAX — Phase 3 : Kanban Restaurateur
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/config.php';

// Sécurité : Seul un Restaurateur (ou Admin) peut modifier l'état des plats en cuisine
$role = $_SESSION['user_role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['Restaurateur', 'Administrateur'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé. Vous devez être restaurateur.']);
    exit;
}

// Récupération des données envoyées via fetch()
$data = json_decode(file_get_contents('php://input'), true);
$id_commande = (int)($data['id_commande'] ?? 0);
$new_statut = trim($data['new_statut'] ?? '');

// Les statuts que le restaurateur est en droit d'appliquer
$allowed_statuts = ['En attente', 'En préparation', 'Prête'];

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
?>