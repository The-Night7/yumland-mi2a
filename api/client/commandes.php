<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';
require_once __DIR__ . '/../includes/panier.php';

// Vérifier si l'utilisateur est connecté et est un client
if (!isLoggedIn() || !hasRole('Client')) {
    redirect('/api/pages/connexion.php');
}

// Traitement de la re-commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'recommander') {
    $id_commande = (int)$_POST['id_commande'];
    
    // Par sécurité, on vérifie que le client tente bien de recommander SA propre commande
    $stmtCheck = $pdo->prepare("SELECT id_commande FROM Commandes WHERE id_commande = ? AND id_client = ?");
    $stmtCheck->execute([$id_commande, $_SESSION['user_id']]);
    if ($stmtCheck->fetch()) {
        $stmtDetails = $pdo->prepare("SELECT id_produit, quantite, options_choisies FROM Contenu_Commandes WHERE id_commande = ?");
        $stmtDetails->execute([$id_commande]);
        $details = $stmtDetails->fetchAll();
        
        // On boucle sur l'ancienne commande et on balance tout dans le panier actuel
        foreach ($details as $item) {
            $options = json_decode($item['options_choisies'], true) ?: [];
            addToCart($item['id_produit'], $item['quantite'], $options);
        }
        header('Location: /api/panier.php');
        exit;
    }
}

// Récupérer les commandes de l'utilisateur
$commandes = getAllCommandes(null, $_SESSION['user_id'], 'DESC');

// Définir la page courante pour le menu actif
$currentPage = 'client_commandes';
$pageTitle = 'Mes Commandes';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="client-section">
    <div class="container">
        <h1>Mes Commandes</h1>
        
        <?php if (empty($commandes)): ?>
            <div class="empty-commandes">
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="/api/pages/carte.php" class="btn-primary">Voir la carte</a>
            </div>
        <?php else: ?>
            <div class="commandes-list">
                <?php foreach ($commandes as $commande): ?>
                    <div class="commande-item card-style">
                        <div class="commande-header">
                            <h3>Commande #<?= $commande['id_commande'] ?></h3>
                            <span class="commande-date">
                                <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?>
                            </span>
                        </div>
                        
                        <div class="commande-details">
                            <p><strong>Statut:</strong> 
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $commande['statut'])) ?>">
                                    <?= htmlspecialchars($commande['statut']) ?>
                                </span>
                            </p>
                            <p><strong>Mode:</strong> <?= htmlspecialchars($commande['mode_retrait'] ?? 'Livraison') ?></p>
                            <?php if (($commande['mode_retrait'] ?? 'livraison') === 'livraison'): ?>
                                <p><strong>Adresse:</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?></p>
                            <?php endif; ?>
                            <p><strong>Montant:</strong> <?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</p>
                        </div>
                        
                        <div class="commande-items">
                            <h4>Détails de la commande</h4>
                            <?php
                            // Récupérer les détails de cette commande spécifique
                            $stmtDetails = $pdo->prepare("SELECT cc.*, p.nom FROM Contenu_Commandes cc JOIN Produits p ON cc.id_produit = p.id_produit WHERE cc.id_commande = ?");
                            $stmtDetails->execute([$commande['id_commande']]);
                            $details = $stmtDetails->fetchAll();
                            ?>
                            <ul>
                                <?php foreach ($details as $detail): ?>
                                    <li>
                                        <span class="item-name"><?= htmlspecialchars($detail['nom']) ?></span>
                                        <span class="item-quantity">x<?= $detail['quantite'] ?></span>
                                        <span class="item-price"><?= number_format($detail['prix_unitaire'] * $detail['quantite'], 2, ',', ' ') ?> €</span>
                                        <?php 
                                        $options = json_decode($detail['options_choisies'], true);
                                        if (!empty($options)): 
                                        ?>
                                            <span class="item-options" style="display: block; font-size: 0.85em; color: var(--color-primary); margin-left: 15px;">
                                                <em>↳ <?= htmlspecialchars(implode(', ', $options)) ?></em>
                                            </span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="commande-actions" style="display: flex; gap: 10px; margin-top: 15px;">
                            <?php if ($commande['statut'] === 'Livrée'): ?>
                                <a href="/api/client/noter.php?commande_id=<?= $commande['id_commande'] ?>" class="btn-secondary" style="padding: 10px 15px; border: 1px solid var(--color-coal-black); color: var(--color-coal-black); text-decoration: none; border-radius: 4px;">⭐ Noter</a>
                            <?php endif; ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="recommander">
                                <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                                <button type="submit" class="btn-primary" style="padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-sync-alt"></i> Recommander
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>