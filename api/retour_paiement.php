<?php
require_once __DIR__ . '/includes/config.php';

$status = $_GET['status'] ?? 'denied';
$transaction = $_GET['transaction'] ?? '';
$montant = $_GET['montant'] ?? 0;

if ($status === 'accepted') {
    try {
        // 1. Créer la commande en base SQL
        $stmt = $pdo->prepare("INSERT INTO Commandes (id_client, prix_total, statut, paiement_statut, cybank_transaction) VALUES (?, ?, 'En préparation', 'Payé', ?)");
        $stmt->execute([$_SESSION['user_id'] ?? 1, $montant, $transaction]);
        
        $id_commande = $pdo->lastInsertId();

        // 2. Transférer le contenu du panier vers Contenu_Commandes
        $stmtItem = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['panier'] as $id_prod => $qte) {
            $stmtP = $pdo->prepare("SELECT prix FROM Produits WHERE id_produit = ?");
            $stmtP->execute([$id_prod]);
            $pu = $stmtP->fetchColumn();
            $stmtItem->execute([$id_commande, $id_prod, $qte, $pu]);
        }

        // 3. Vider le panier
        unset($_SESSION['panier']);

        header('Location: ../public/html/profil.html?success=commande_validee');
    } catch (Exception $e) {
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
} else {
    header('Location: ../public/html/panier.html?error=paiement_refuse');
}
?>