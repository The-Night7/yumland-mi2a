<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/commandes.php';

// Traitement de l'action de livraison
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'terminee') {
    updateCommandeStatus((int)$_POST['id_commande'], 'Livrée');
    header('Location: /api/livreur/livraisons.php');
    exit;
}

// Récupération du livreur connecté
$livreur_id = $_SESSION['user_id'] ?? null;
if (!$livreur_id) {
    $stmtLiv = $pdo->query("SELECT id_user FROM Utilisateurs WHERE role = 'Livreur' LIMIT 1");
    $liv = $stmtLiv->fetch();
    $livreur_id = $liv ? $liv['id_user'] : 9;
}
$mes_livraisons = getCommandesByLivreur($livreur_id);

?>

<section class="container" style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">Mes Courses</h1>
    </div>
    
    <?php if (empty($mes_livraisons)): ?>
        <div class="alert alert-success">
            Aucune livraison en cours. En attente de courses...
        </div>
    <?php endif; ?>

    <?php foreach ($mes_livraisons as $livraison): ?>
        <article class="card-style" style="padding: 20px; text-align: left; margin-bottom: 20px;">
            <h2 style="font-size: 1.5rem; margin-bottom: 5px;">Commande #<?= $livraison['id_commande'] ?></h2>
            <?php $adresse_a_afficher = !empty($livraison['adresse_livraison']) ? $livraison['adresse_livraison'] : (!empty($livraison['client_adresse']) ? $livraison['client_adresse'] : 'Adresse non spécifiée'); ?>
            <p style="font-size: 1.2rem; color: #555;">
                📍 <?= htmlspecialchars($adresse_a_afficher) ?>
            </p>
            
            <!-- Boutons XXL (Hauteur mini 60px pour gants selon le README) -->
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 25px;">
                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($adresse_a_afficher) ?>" target="_blank" class="btn-primary" style="padding: 20px; font-size: 1.2rem; font-weight: bold; background: #4285F4; text-align: center;">
                    🗺️ OUVRIR DANS MAPS
                </a>
                
                <?php if (!empty($livraison['client_tel'])): ?>
                <a href="tel:<?= htmlspecialchars($livraison['client_tel']) ?>" class="btn-primary" style="padding: 20px; font-size: 1.2rem; font-weight: bold; background: #e67e22; text-align: center;">
                    📞 APPELER LE CLIENT (<?= htmlspecialchars(strtoupper($livraison['client_nom'])) ?>)
                </a>
                <?php endif; ?>
                
                <form method="POST" style="margin: 0; display: flex; flex-direction: column; gap: 15px;">
                    <input type="hidden" name="action" value="terminee">
                    <input type="hidden" name="id_commande" value="<?= $livraison['id_commande'] ?>">
                    <button type="submit" class="btn-primary" style="padding: 20px; font-size: 1.2rem; font-weight: bold; background: #2ecc71; border: none; cursor: pointer;">
                        ✅ MARQUER COMME LIVRÉE
                    </button>
                    <button type="button" class="btn-primary" style="padding: 20px; font-size: 1.2rem; font-weight: bold; background: var(--color-coal-black); border: none; cursor: pointer;" onclick="alert('Contactez le support :\n- Myriam Bensaid : 06 68 39 92 06\n- Sheryne Ouarghi : 06 17 67 77 02')">
                        ❌ PROBLÈME DE LIVRAISON
                    </button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>