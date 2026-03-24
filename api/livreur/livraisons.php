<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';

// Vérifier si l'utilisateur est connecté et est un livreur
if (!isLoggedIn() || !hasRole('Livreur')) {
    redirect('/api/pages/connexion.php');
}

// Récupérer les commandes assignées à ce livreur
$commandes = getCommandesByLivreur($_SESSION['user_id']);

// Définir la page courante pour le menu actif
$currentPage = 'livreur_livraisons';
$pageTitle = 'Mes Livraisons';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="livreur-section">
    <div class="container">
        <h1>Mes Livraisons</h1>
        
        <?php if (empty($commandes)): ?>
            <div class="empty-livraisons card-style">
                <p>Vous n'avez aucune livraison en cours.</p>
                <p>Les nouvelles livraisons vous seront assignées par le restaurateur.</p>
            </div>
        <?php else: ?>
            <div class="livraisons-list">
                <?php foreach ($commandes as $commande): ?>
                    <div class="livraison-card card-style">
                        <div class="livraison-header">
                            <h3>Commande #<?= $commande['id'] ?></h3>
                            <span class="livraison-date">
                                <?= date('d/m/Y H:i', strtotime($commande['date'])) ?>
                            </span>
                        </div>
                        
                        <div class="livraison-details">
                            <p><strong>Statut:</strong> 
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $commande['status'])) ?>">
                                    <?= htmlspecialchars($commande['status']) ?>
                                </span>
                            </p>
                            <p><strong>Adresse de livraison:</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?></p>
                            <p><strong>Montant:</strong> <?= number_format($commande['montant_total'], 2, ',', ' ') ?> €</p>
                        </div>
                        
                        <div class="livraison-items">
                            <h4>Articles à livrer</h4>
                            <ul>
                                <?php foreach ($commande['details'] as $detail): ?>
                                    <?php $plat = getPlatById($detail['plat_id']); ?>
                                    <li>
                                        <span class="item-quantity"><?= $detail['quantite'] ?>x</span>
                                        <span class="item-name"><?= htmlspecialchars($plat['nom']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="livraison-actions">
                            <!-- Ces boutons sont visuels uniquement pour la Phase 2 -->
                            <button class="btn-success" disabled>Marquer comme livrée</button>
                            <button class="btn-danger" disabled>Signaler un problème</button>
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