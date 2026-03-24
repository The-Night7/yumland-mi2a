<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/commandes.php';

// Simulation : Récupérer toutes les commandes pour la cuisine
$commandes = getAllCommandes(); 
?>

<section class="container">
    <h1>Flux de Cuisine (Restaurateur)</h1>
    <p>Tableau de bord des commandes à préparer.</p>

    <div class="gallery-grid">
        <?php foreach ($commandes as $cmd): ?>
            <article class="card-style" style="padding: 15px; text-align: left;">
                <h3>Commande #<?= $cmd['id_commande'] ?></h3>
                <p><strong>Date :</strong> <?= date('H:i', strtotime($cmd['date_commande'])) ?></p>
                <p><strong>Statut :</strong> 
                    <span style="color: var(--color-grill-red); font-weight: bold;">
                        <?= htmlspecialchars($cmd['statut']) ?>
                    </span>
                </p>
                
                <!-- Simulation des détails (Dans la phase 3, on fera un JOIN avec Contenu_Commandes) -->
                <div style="background: var(--color-sauce-cream); padding: 10px; border-radius: 4px; margin: 10px 0;">
                    <em>Détails des plats à préparer...</em>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                    <button class="btn-primary" style="background: #e67e22;">
                        Passer "En préparation"
                    </button>
                    <button class="btn-primary" style="background: #27ae60;">
                        Commande Prête
                    </button>
                    <button class="btn-primary" style="background: var(--color-coal-black);">
                        Attribuer à un livreur
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (empty($commandes)): ?>
            <p>Aucune commande pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>