<?php
// api/admin/toggle_block.php
// Endpoint AJAX — Bloquer/Débloquer un utilisateur (Phase 3)
header('Content-Type: application/json');
session_start();

// Vérification : seul un admin peut appeler cet endpoint
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['type'] ?? '';

if (!isset($_SESSION['user_id']) || $role !== 'Administrateur') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

require_once __DIR__ . '/../includes/config.php';

$data    = json_decode(file_get_contents('php://input'), true);
$cible_id = (int)($data['id_user'] ?? 0);

if ($cible_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
    exit;
}

// Empêcher l'admin de se bloquer lui-même
if ($cible_id === (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous bloquer vous-même.']);
    exit;
}

try {
    // Récupérer le statut actuel
    $stmt = $pdo->prepare("SELECT statut FROM Utilisateurs WHERE id_user = ?");
    $stmt->execute([$cible_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        exit;
    }

    // Inverser le statut
    $newStatut = ($user['statut'] === 'Bloqué') ? 'Actif' : 'Bloqué';

    $stmt = $pdo->prepare("UPDATE Utilisateurs SET statut = ? WHERE id_user = ?");
    $stmt->execute([$newStatut, $cible_id]);

    // ── Kill session de l'utilisateur bloqué ──────────────
    // On stocke les utilisateurs bloqués dans une table de sessions révoquées
    // OU on utilise un flag en base que chaque page vérifie (méthode simple)
    // Ici on utilise la méthode "flag en base" : chaque page PHP vérifie le statut
    // => Rien de plus à faire ici car header.php doit vérifier le statut à chaque requête

    echo json_encode([
        'success'    => true,
        'new_statut' => $newStatut,
        'message'    => "Utilisateur " . ($newStatut === 'Bloqué' ? 'bloqué' : 'débloqué') . " avec succès."
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données.']);
}

?>