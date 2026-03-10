<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';

// Vérifier si l'utilisateur est connecté et est un restaurateur
if (!isLoggedIn() || !hasRole('restaurateur')) {
    redirect('/public/html/connexion.php');
}

// Récupérer les commandes à traiter
$commandes_preparation = getAllCommandes('en préparation');
$commandes_prets = getAllCommandes('prêt');
$commandes_livraison = getAllCommandes('en livraison');

// Définir la page courante pour le menu actif
$currentPage = 'restaurateur_commandes';
$pageTitle = 'Gestion des Commandes';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="restaurateur-section">
    <div class="container">
        <h1>Gestion des Commandes</h1>
        
        <div class="commandes-tabs">
            <ul class="tabs-nav">
                <li class="active"><a href="#tab-preparation">En préparation (<?= count($commandes_preparation) ?>)</a></li>
                <li><a href="#tab-prets">Prêts (<?= count($commandes_prets) ?>)</a></li>
                <li><a href="#tab-livraison">En livraison (<?= count($commandes_livraison) ?>)</a></li>
            </ul>
            
            <div class="tabs-content">
                <!-- Tab: Commandes en préparation -->
                <div id="tab-preparation" class="tab-pane active">
                    <?php if (empty($commandes_preparation)): ?>
                        <p class="empty-tab">Aucune commande en préparation.</p>
                    <?php else: ?>
                        <div class="commandes-list">
                            <?php foreach ($commandes_preparation as $commande): ?>
                                <div class="commande-card card-style">
                                    <div class="commande-header">
                                        <h3>Commande #<?= $commande['id'] ?></h3>
                                        <span class="commande-date">
                                            <?= date('d/m/Y H:i', strtotime($commande['date'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="commande-details">
                                        <p><strong>Mode:</strong> <?= htmlspecialchars($commande['mode']) ?></p>
                                        <p><strong>Montant:</strong> <?= number_format($commande['montant_total'], 2, ',', ' ') ?> €</p>
                                    </div>
                                    
                                    <div class="commande-items">
                                        <h4>Articles à préparer</h4>
                                        <ul>
                                            <?php foreach ($commande['details'] as $detail): ?>
                                                <?php $plat = getPlatById($detail['plat_id']); ?>
                                                <li>
                                                    <span class="item-quantity"><?= $detail['quantite'] ?>x</span>
                                                    <span class="item-name"><?= htmlspecialchars($plat['nom']) ?></span>
                                                    <?php if (!empty($detail['options'])): ?>
                                                        <span class="item-options">
                                                            (<?= htmlspecialchars(implode(', ', $detail['options'])) ?>)
                                                        </span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    
                                    <div class="commande-actions">
                                        <!-- Ces boutons sont visuels uniquement pour la Phase 2 -->
                                        <button class="btn-primary" disabled>Marquer comme prêt</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Commandes prêtes -->
                <div id="tab-prets" class="tab-pane">
                    <?php if (empty($commandes_prets)): ?>
                        <p class="empty-tab">Aucune commande prête.</p>
                    <?php else: ?>
                        <div class="commandes-list">
                            <?php foreach ($commandes_prets as $commande): ?>
                                <div class="commande-card card-style">
                                    <div class="commande-header">
                                        <h3>Commande #<?= $commande['id'] ?></h3>
                                        <span class="commande-date">
                                            <?= date('d/m/Y H:i', strtotime($commande['date'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="commande-details">
                                        <p><strong>Mode:</strong> <?= htmlspecialchars($commande['mode']) ?></p>
                                        <p><strong>Montant:</strong> <?= number_format($commande['montant_total'], 2, ',', ' ') ?> €</p>
                                    </div>
                                    
                                    <div class="commande-items">
                                        <h4>Articles préparés</h4>
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
                                    
                                    <?php if ($commande['mode'] === 'livraison'): ?>
                                        <div class="commande-actions">
                                            <!-- Ces boutons sont visuels uniquement pour la Phase 2 -->
                                            <button class="btn-primary" disabled>Assigner un livreur</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Commandes en livraison -->
                <div id="tab-livraison" class="tab-pane">
                    <?php if (empty($commandes_livraison)): ?>
                        <p class="empty-tab">Aucune commande en livraison.</p>
                    <?php else: ?>
                        <div class="commandes-list">
                            <?php foreach ($commandes_livraison as $commande): ?>
                                <div class="commande-card card-style">
                                    <div class="commande-header">
                                        <h3>Commande #<?= $commande['id'] ?></h3>
                                        <span class="commande-date">
                                            <?= date('d/m/Y H:i', strtotime($commande['date'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="commande-details">
                                        <p><strong>Mode:</strong> <?= htmlspecialchars($commande['mode']) ?></p>
                                        <p><strong>Adresse:</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?></p>
                                        <p><strong>Montant:</strong> <?= number_format($commande['montant_total'], 2, ',', ' ') ?> €</p>
                                    </div>
                                    
                                    <div class="commande-info">
                                        <p><strong>Livreur:</strong> 
                                            <?php 
                                            $livreur = getUserById($commande['livreur_id']);
                                            echo $livreur ? htmlspecialchars($livreur['prenom'] . ' ' . $livreur['nom']) : 'Non assigné';
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Script pour gérer les onglets
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tabs-nav a');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Masquer tous les panneaux et désactiver tous les onglets
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });
                
                document.querySelectorAll('.tabs-nav li').forEach(li => {
                    li.classList.remove('active');
                });
                
                // Afficher le panneau cible et activer l'onglet
                const target = this.getAttribute('href');
                document.querySelector(target).classList.add('active');
                this.parentElement.classList.add('active');
            });
        });
    });
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>