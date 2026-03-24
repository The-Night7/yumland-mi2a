<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/commandes.php';

// Récupération du livreur connecté
$livreur_id = $_SESSION['user_id'] ?? 9;
$mes_livraisons = getCommandesByLivreur($livreur_id);

// 🛠️ FAKE COMMANDE : Si aucune commande, on en simule une pour valider l'interface
if (empty($mes_livraisons)) {
    $mes_livraisons[] = [
        'id_commande' => '9999 (Démo)',
        'adresse_livraison' => 'Avenue du Parc, 95000 Cergy',
        'statut' => 'En livraison',
        'prix_total' => 24.50
    ];
}
?>

<section class="container" style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">Mes Courses</h1>
        <button id="toggle-dark-mode" class="btn-primary" style="padding: 10px; font-size: 0.9rem; border-radius: 20px; background-color: var(--color-coal-black);">🌓 Mode Nuit</button>
    </div>
    
    <?php if (empty($mes_livraisons)): ?>
        <div class="alert alert-success">
            Aucune livraison en cours. En attente de courses...
        </div>
    <?php endif; ?>

    <?php foreach ($mes_livraisons as $livraison): ?>
        <article class="card-style" style="padding: 20px; text-align: left; margin-bottom: 20px;">
            <h2 style="font-size: 1.5rem; margin-bottom: 5px;">Commande #<?= $livraison['id_commande'] ?></h2>
            <p style="font-size: 1.2rem; color: #555;">
                📍 <?= htmlspecialchars($livraison['adresse_livraison'] ?? 'Adresse non spécifiée') ?>
            </p>
            
            <!-- Boutons XXL (Hauteur mini 60px pour gants selon le README) -->
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 25px;">
                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($livraison['adresse_livraison'] ?? '') ?>" target="_blank" class="btn-primary" style="padding: 20px; font-size: 1.2rem; font-weight: bold; background: #4285F4; text-align: center;">
                    🗺️ OUVRIR DANS MAPS
                </a>
                <button class="btn-primary" style="padding: 20px; font-size: 1.2rem; font-weight: bold; background: #2ecc71;">
                    ✅ MARQUER COMME LIVRÉE
                </button>
                <button class="btn-primary" style="padding: 20px; font-size: 1.2rem; font-weight: bold; background: var(--color-coal-black);">
                    ❌ ABANDONNER / PROBLÈME
                </button>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<script>
// Script pour le mode nuit (Livreur)
document.getElementById('toggle-dark-mode').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    
    // Sauvegarder le choix dans le navigateur
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
});

// Appliquer le mode nuit au chargement si déjà activé par le passé
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>