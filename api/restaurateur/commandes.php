<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/commandes.php';

// Vérifier si l'utilisateur est connecté et est un restaurateur
if (!isLoggedIn() || !hasRole('Restaurateur')) {
    redirect('/api/pages/connexion.php');
}

// Traitement des actions (Changement de statut)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id_commande = (int)$_POST['id_commande'];
    if ($_POST['action'] === 'preparer') {
        updateCommandeStatus($id_commande, 'En préparation');
    } elseif ($_POST['action'] === 'prete') {
        updateCommandeStatus($id_commande, 'Prête');
    } elseif ($_POST['action'] === 'livrer') {
        // Assigner au livreur de démo (ID 9) ou au livreur connecté
        assignLivreur($id_commande, 9);
    } elseif ($_POST['action'] === 'servie') {
        updateCommandeStatus($id_commande, 'Livrée');
    }
    header('Location: /api/restaurateur/commandes.php');
    exit;
}

// Récupérer les commandes à traiter
$commandes_attente = getAllCommandes('En attente');
$commandes_preparation = getAllCommandes('En préparation');
$commandes_pretes = getAllCommandes('Prête');

// Définir la page courante pour le menu actif
$currentPage = 'restaurateur_commandes';
$pageTitle = 'Gestion des Commandes';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* Affichage Kanban KDS (Kitchen Display System) pour le Restaurateur */
    .kanban-board { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
    .kanban-col { background: var(--color-sauce-cream); border-radius: 8px; padding: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); min-height: 60vh; }
    .kanban-col h2 { color: white; padding: 15px; text-align: center; border-radius: 6px; margin-top: 0; font-size: 1.2rem; }
    .col-attente h2 { background: var(--color-grill-red); }
    .col-preparation h2 { background: #e67e22; }
    .col-pretes h2 { background: #27ae60; }
    
    .commande-card { background: white; border: 1px solid #eee; border-left: 5px solid var(--color-coal-black); margin-bottom: 15px; padding: 15px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .col-attente .commande-card { border-left-color: var(--color-grill-red); }
    .col-preparation .commande-card { border-left-color: #e67e22; }
    .col-pretes .commande-card { border-left-color: #27ae60; }
    
    .commande-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
    .commande-header h3 { margin: 0; font-size: 1.1rem; }
    .commande-time { font-weight: bold; color: #666; font-size: 0.9rem; }
    
    .item-list { list-style: none; padding: 0; margin: 0; }
    .item-list li { margin-bottom: 8px; font-size: 1.05rem; }
    .item-qty { font-weight: bold; background: #eee; padding: 2px 6px; border-radius: 4px; margin-right: 5px; }
    .item-opts { display: block; font-size: 0.85rem; color: var(--color-grill-red); margin-left: 30px; font-style: italic; }
    
    .kanban-btn { width: 100%; padding: 10px; border: none; border-radius: 4px; font-weight: bold; color: white; cursor: pointer; margin-top: 15px; transition: 0.2s; }
    .kanban-btn:hover { opacity: 0.9; }
    
    @media (max-width: 900px) {
        .kanban-board { grid-template-columns: 1fr; }
    }
</style>

<section class="restaurateur-section">
    <div class="container" style="max-width: 1400px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>👨‍🍳 Écran Cuisine (KDS)</h1>
            <p><strong>Heure :</strong> <span id="clock"><?= date('H:i:s') ?></span></p>
        </div>
        
        <div class="kanban-board">
            
            <!-- COLONNE 1 : EN ATTENTE -->
            <div class="kanban-col col-attente">
                <h2>À Préparer (<?= count($commandes_attente) ?>)</h2>
                <?php foreach ($commandes_attente as $cmd): ?>
                    <div class="commande-card">
                        <div class="commande-header">
                            <h3>#<?= $cmd['id_commande'] ?> <?= $cmd['mode_retrait'] === 'sur place' ? '🍽️' : '🛵' ?></h3>
                            <span class="commande-time"><?= date('H:i', strtotime($cmd['date_commande'])) ?></span>
                        </div>
                        <ul class="item-list">
                            <?php
                            $stmtDetails = $pdo->prepare("SELECT cc.*, p.nom FROM Contenu_Commandes cc JOIN Produits p ON cc.id_produit = p.id_produit WHERE cc.id_commande = ?");
                            $stmtDetails->execute([$cmd['id_commande']]);
                            foreach ($stmtDetails->fetchAll() as $detail):
                            ?>
                                <li>
                                    <span class="item-qty"><?= $detail['quantite'] ?>x</span> <?= htmlspecialchars($detail['nom']) ?>
                                    <?php if (!empty($detail['options_choisies'])): ?>
                                        <span class="item-opts">Info: <?= htmlspecialchars($detail['options_choisies']) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" style="margin:0; margin-top: 15px;">
                            <input type="hidden" name="action" value="preparer">
                            <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                            <button type="submit" class="kanban-btn" style="background: #e67e22; margin-top: 0;">
                                Passer en préparation 🍳
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- COLONNE 2 : EN PRÉPARATION -->
            <div class="kanban-col col-preparation">
                <h2>En Cours de Cuisson (<?= count($commandes_preparation) ?>)</h2>
                <?php foreach ($commandes_preparation as $cmd): ?>
                    <div class="commande-card">
                        <div class="commande-header">
                            <h3>#<?= $cmd['id_commande'] ?> <?= $cmd['mode_retrait'] === 'sur place' ? '🍽️' : '🛵' ?></h3>
                            <span class="commande-time"><?= date('H:i', strtotime($cmd['date_commande'])) ?></span>
                        </div>
                        <ul class="item-list">
                            <?php
                            $stmtDetails->execute([$cmd['id_commande']]);
                            foreach ($stmtDetails->fetchAll() as $detail):
                            ?>
                                <li><span class="item-qty"><?= $detail['quantite'] ?>x</span> <?= htmlspecialchars($detail['nom']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" style="margin:0; margin-top: 15px;">
                            <input type="hidden" name="action" value="prete">
                            <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                            <button type="submit" class="kanban-btn" style="background: #27ae60; margin-top: 0;">
                                Marquer comme Prête ✅
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- COLONNE 3 : PRÊTES -->
            <div class="kanban-col col-pretes">
                <h2>Prêtes / Au Comptoir (<?= count($commandes_pretes) ?>)</h2>
                <?php foreach ($commandes_pretes as $cmd): ?>
                    <div class="commande-card">
                        <div class="commande-header">
                            <h3>#<?= $cmd['id_commande'] ?> <?= $cmd['mode_retrait'] === 'sur place' ? '🍽️' : '🛵' ?></h3>
                            <span class="commande-time"><?= date('H:i', strtotime($cmd['date_commande'])) ?></span>
                        </div>
                        <p style="text-align: center; color: #27ae60; font-weight: bold;">En attente de retrait</p>
                        
                        <?php if(($cmd['mode_retrait'] ?? 'livraison') === 'livraison'): ?>
                            <form method="POST" style="margin:0; margin-top: 15px;">
                                <input type="hidden" name="action" value="livrer">
                                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                                <button type="submit" class="kanban-btn" style="background: var(--color-coal-black); margin-top: 0;">🚴 Assigner un livreur</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="margin:0; margin-top: 15px;">
                                <input type="hidden" name="action" value="servie">
                                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                                <button type="submit" class="kanban-btn" style="background: var(--color-coal-black); margin-top: 0;">🍽️ Marquer comme Servie</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>

<script>
    // Horloge temps réel pour la cuisine
    setInterval(() => {
        const now = new Date();
        document.getElementById('clock').textContent = now.toLocaleTimeString('fr-FR');
    }, 1000);
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>