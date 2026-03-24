<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/panier.php';

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
        foreach ($_SESSION['cart']['items'] as $item) {
            $stmtItem->execute([$id_commande, $item['plat_id'], $item['quantite'], $item['prix_unitaire']]);
        }

        // 3. Vider le panier
        clearCart();

        header('Location: /api/client/commandes.php?success=commande_validee');
    } catch (Exception $e) {
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
} else {
    header('Location: /api/panier.php?error=paiement_refuse');
}
?>