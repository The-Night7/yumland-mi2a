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
    // Action pour générer une fausse commande de test
    if ($_POST['action'] === 'demo') {
            $pdo->exec("INSERT INTO Commandes (id_client, prix_total, statut, adresse_livraison) VALUES (1, 24.50, 'En attente', '12 Avenue du Parc, 95000 Cergy')");
        $new_id = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES ($new_id, 1, 1, 15.90, 'Cuisson à point, Sauce BBQ')");
        header('Location: /api/restaurateur/commandes.php');
        exit;
    }

    $id_commande = (int)$_POST['id_commande'];
    if ($_POST['action'] === 'preparer') {
        updateCommandeStatus($id_commande, 'En préparation');
    } elseif ($_POST['action'] === 'prete') {
        updateCommandeStatus($id_commande, 'Prête');
    } elseif ($_POST['action'] === 'livrer') {
            // Assigner au premier livreur disponible dans la base (évite les conflits d'ID)
            $stmtLiv = $pdo->query("SELECT id_user FROM Utilisateurs WHERE role = 'Livreur' LIMIT 1");
            $liv = $stmtLiv->fetch();
            assignLivreur($id_commande, $liv ? $liv['id_user'] : 9);
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
    /* Intégration du design HTML Mockup (Cuisine - Le Grand Miam) */
    .resto-header { background: #222; padding: 1rem; color: white; text-align: center; border-radius: 8px; margin-bottom: 20px; }
    .resto-header h1 { margin: 0; font-size: 2rem; color: white; }
    .resto-time { color: #ff6b00; font-size: 1.2rem; font-weight: bold; margin-top: 10px; display: block; }

    .kitchen-board { display: flex; flex-wrap: wrap; gap: 20px; min-height: 70vh; }
    @media (min-width: 900px) { .kitchen-board { flex-wrap: nowrap; } }
    
    .column { flex: 1; min-width: 300px; background: #f9f9f9; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); padding: 15px; }
    .column h2 { padding: 15px; margin: -15px -15px 15px -15px; text-align: center; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px; font-size: 1.3rem; }
    
    .col-waiting h2 { background: var(--color-primary, #d32f2f); }
    .col-prep h2 { background: #f57c00; }
    .col-ready h2 { background: #388e3c; }
    
    .order-card { background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .col-waiting .order-card { border-left: 5px solid var(--color-primary, #d32f2f); }
    .col-prep .order-card { border-left: 5px solid #f57c00; }
    .col-ready .order-card { border-left: 5px solid #388e3c; }

    .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; font-size: 1.2rem; font-weight: bold; color: #333; }
    .order-items { list-style: none; padding: 0; margin: 0 0 15px 0; }
    .order-items li { margin-bottom: 8px; font-size: 1.05rem; }
    
    .item-qty { font-weight: bold; background: #eee; padding: 2px 6px; border-radius: 4px; margin-right: 5px; }
    .item-opts { display: block; font-size: 0.85rem; color: var(--color-primary, #d32f2f); margin-left: 30px; font-style: italic; }
    
    .btn-move { width: 100%; padding: 12px; font-size: 1.1rem; border: none; border-radius: 4px; font-weight: bold; color: white; cursor: pointer; transition: opacity 0.2s; margin-top: 10px; }
    .btn-move:hover { opacity: 0.9; }
    .btn-start { background: #f57c00; }
    .btn-ready { background: #388e3c; }
    .btn-deliver { background: #222; }
</style>

<section class="restaurateur-section">
    <div class="container" style="max-width: 1400px;">
        <header class="resto-header">
            <div style="display:flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;"></div>
                <div style="flex: 2; text-align: center;">
                    <h1>👨‍🍳 CUISINE - LE GRAND MIAM</h1>
                    <span class="resto-time" id="clock"><?= date('H:i:s') ?></span>
                </div>
                <div style="flex: 1; text-align: right;">
                    <form method="POST" style="margin:0;"><input type="hidden" name="action" value="demo"><button type="submit" class="btn-primary" style="background:#ff6b00; border:none; padding:10px 15px; border-radius:5px; cursor:pointer; font-weight:bold; color:white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">+ Fausse Commande</button></form>
                </div>
            </div>
        </header>
        
        <?php 
        // Préparation unique de la requête pour éviter les crashs si une colonne est vide
        $stmtDetails = $pdo->prepare("SELECT cc.*, p.nom FROM Contenu_Commandes cc LEFT JOIN Produits p ON cc.id_produit = p.id_produit WHERE cc.id_commande = ?");
        ?>
        <main class="kitchen-board">
            
            <!-- COLONNE 1 : EN ATTENTE -->
            <section class="column col-waiting" id="col-waiting">
                <h2>🔥 En Attente (<?= count($commandes_attente) ?>)</h2>
                <div id="list-waiting">
                <?php if (empty($commandes_attente)): ?>
                    <p style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px 0;">Aucune commande en attente.</p>
                <?php endif; ?>
                <?php foreach ($commandes_attente as $cmd): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span>#<?= $cmd['id_commande'] ?> <?= ($cmd['mode_retrait'] ?? 'livraison') === 'sur place' ? '🍽️' : '🛵' ?></span>
                            <span><?= date('H:i', strtotime($cmd['date_commande'] ?? 'now')) ?></span>
                        </div>
                        <ul class="order-items">
                            <?php
                            $stmtDetails->execute([$cmd['id_commande']]);
                            foreach ($stmtDetails->fetchAll() as $detail):
                            ?>
                                <li>
                                    <span class="item-qty"><?= $detail['quantite'] ?>x</span> <?= htmlspecialchars($detail['nom'] ?? 'Produit inconnu') ?>
                                    <?php if (!empty($detail['options_choisies'])): ?>
                                        <span class="item-opts">Info: <?= htmlspecialchars($detail['options_choisies']) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="action" value="preparer">
                            <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                            <button type="submit" class="btn-move btn-start">
                                Lancer Préparation
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

            <!-- COLONNE 2 : EN PRÉPARATION -->
            <section class="column col-prep" id="col-prep">
                <h2>🔪 En Préparation (<?= count($commandes_preparation) ?>)</h2>
                <div id="list-prep">
                <?php if (empty($commandes_preparation)): ?>
                    <p style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px 0;">Aucune commande en préparation.</p>
                <?php endif; ?>
                <?php foreach ($commandes_preparation as $cmd): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span>#<?= $cmd['id_commande'] ?> <?= ($cmd['mode_retrait'] ?? 'livraison') === 'sur place' ? '🍽️' : '🛵' ?></span>
                            <span><?= date('H:i', strtotime($cmd['date_commande'] ?? 'now')) ?></span>
                        </div>
                        <ul class="order-items">
                            <?php
                            $stmtDetails->execute([$cmd['id_commande']]);
                            foreach ($stmtDetails->fetchAll() as $detail):
                            ?>
                                <li><span class="item-qty"><?= $detail['quantite'] ?>x</span> <?= htmlspecialchars($detail['nom'] ?? 'Produit inconnu') ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="action" value="prete">
                            <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                            <button type="submit" class="btn-move btn-ready">
                                Commande Prête
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

            <!-- COLONNE 3 : PRÊTES -->
            <section class="column col-ready" id="col-ready">
                <h2>✅ Prêt à livrer (<?= count($commandes_pretes) ?>)</h2>
                <div id="list-ready">
                <?php if (empty($commandes_pretes)): ?>
                    <p style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px 0;">Aucune commande prête.</p>
                <?php endif; ?>
                <?php foreach ($commandes_pretes as $cmd): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span>#<?= $cmd['id_commande'] ?> <?= ($cmd['mode_retrait'] ?? 'livraison') === 'sur place' ? '🍽️' : '🛵' ?></span>
                            <span><?= date('H:i', strtotime($cmd['date_commande'] ?? 'now')) ?></span>
                        </div>
                        
                        <?php if(($cmd['mode_retrait'] ?? 'livraison') === 'livraison'): ?>
                            <div style="text-align:center; color:green; font-weight:bold; margin-bottom: 10px;">En attente livreur</div>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="livrer">
                                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                                <button type="submit" class="btn-move btn-deliver">🚴 Assigner un livreur</button>
                            </form>
                        <?php else: ?>
                            <div style="text-align:center; color:green; font-weight:bold; margin-bottom: 10px;">En attente client (Sur place)</div>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="servie">
                                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                                <button type="submit" class="btn-move btn-deliver">🍽️ Marquer comme Servie</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

        </main>
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