<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/panier.php';
require_once __DIR__ . '/includes/getapikey.php';

// Récupération des données envoyées par l'interface de l'école (GET)
$transaction = $_GET['transaction'] ?? '';
$montant = $_GET['montant'] ?? 0;
$vendeur = $_GET['vendeur'] ?? '';
$control = $_GET['control'] ?? '';

// CYBank peut renvoyer 'status' (comme dans l'exemple) ou 'statut' (comme dans le texte doc)
$statut = $_GET['statut'] ?? $_GET['status'] ?? 'declined'; 

// On recalcule la clé MD5 avec les données reçues. Si ça correspond à ce qu'envoie la banque,
// c'est que l'URL n'a pas été trafiquée par un petit malin.
$api_key = getAPIKey($vendeur);
$expected_control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");

// On vire le préfixe "MI2A" (4 lettres) pour retrouver notre vrai ID de BDD auto-incrémenté
$id_commande = (int)substr($transaction, 4);

// Vérification ultime : bonne signature + statut ok + ID cohérent
if ($control === $expected_control && $statut === 'accepted' && $id_commande > 0) {
    try {
        // Sécurisation des opérations multiples via transaction SQL
        $pdo->beginTransaction();

        // Validation de la commande
        $stmt = $pdo->prepare("UPDATE Commandes SET statut = 'En attente', paiement_statut = 'Payé', cybank_transaction = ? WHERE id_commande = ?");
        $stmt->execute([$transaction, $id_commande]);
        
        // Historisation de la transaction
        $stmtPaiement = $pdo->prepare("INSERT INTO Paiements (id_commande, id_client, montant, cybank_transaction_id) VALUES (?, ?, ?, ?)");
        $stmtPaiement->execute([$id_commande, $_SESSION['user_id'] ?? 1, $montant, $transaction]);

        // Attribution des points de fidélité
        $miams_gagnes = floor($montant * 10);
        $stmtMiams = $pdo->prepare("UPDATE Utilisateurs SET solde_miams = solde_miams + ?, total_miams_historique = total_miams_historique + ? WHERE id_user = ?");
        $stmtMiams->execute([$miams_gagnes, $miams_gagnes, $_SESSION['user_id'] ?? 1]);

        $pdo->commit();

        clearCart();

        header('Location: /api/client/commandes.php?success=commande_validee');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
} else {
    // En cas d'échec du paiement, la commande passe en annulée
    if ($id_commande > 0) {
        $pdo->prepare("UPDATE Commandes SET statut = 'Annulée', paiement_statut = 'Échec' WHERE id_commande = ?")->execute([$id_commande]);
    }
    header('Location: /api/panier.php?error=paiement_refuse');
    exit;
}
?>