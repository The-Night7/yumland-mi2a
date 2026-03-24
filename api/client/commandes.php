<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';

// Vérifier si l'utilisateur est connecté et est un client
if (!isLoggedIn() || !hasRole('Client')) {
    redirect('/api/pages/connexion.php');
}

// Récupérer les commandes de l'utilisateur
$commandes = getAllCommandes(null, $_SESSION['user_id']);

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
                                        <?php if (!empty($detail['options_choisies'])): ?>
                                            <span class="item-options">
                                                Options: <?= htmlspecialchars($detail['options_choisies']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <?php if ($commande['statut'] === 'Livrée'): ?>
                            <div class="commande-actions">
                                <a href="/api/client/noter.php?commande_id=<?= $commande['id_commande'] ?>" class="btn-secondary">Noter cette commande</a>
                            </div>
                        <?php endif; ?>
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