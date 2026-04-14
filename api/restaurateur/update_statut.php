<?php
// api/restaurateur/update_statut.php
// Endpoint AJAX — Changement de statut d'une commande
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Restaurateur') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

require_once __DIR__ . '/../includes/config.php';

$data        = json_decode(file_get_contents('php://input'), true);
$commande_id = (int)($data['id_commande'] ?? 0);
$new_statut  = trim($data['statut'] ?? '');
$livreur_id  = isset($data['id_livreur']) ? (int)$data['id_livreur'] : null;

$statuts_valides = ['En préparation', 'Prête', 'En livraison'];

if ($commande_id <= 0 || !in_array($new_statut, $statuts_valides)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

try {
    if ($new_statut === 'En livraison' && $livreur_id) {
        // Assigner un livreur ET changer le statut
        $stmt = $pdo->prepare(
            "UPDATE Commandes SET statut = ?, id_livreur = ? WHERE id_commande = ?"
        );
        $stmt->execute([$new_statut, $livreur_id, $commande_id]);
    } else {
        $stmt = $pdo->prepare(
            "UPDATE Commandes SET statut = ? WHERE id_commande = ?"
        );
        $stmt->execute([$new_statut, $commande_id]);
    }

    echo json_encode([
        'success'    => true,
        'new_statut' => $new_statut,
        'message'    => "Commande #$commande_id passée en : $new_statut"
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données.']);
}

?>