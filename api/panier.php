<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/panier.php';
require_once __DIR__ . '/includes/plats.php';

// Traiter les actions sur le panier
$message = '';

// Action: Supprimer un élément du panier
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (removeFromCart($index)) {
        $message = 'L\'article a été retiré du panier.';
    }
}

// Action: Mettre à jour la quantité
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Erreur de sécurité, veuillez réessayer.';
    } else {
        foreach ($_POST['quantite'] as $index => $quantite) {
            updateCartQuantity($index, (int)$quantite);
        }
        $message = 'Le panier a été mis à jour.';
    }
}

// Action: Vider le panier
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    clearCart();
    $message = 'Votre panier a été vidé.';
}

// Récupérer le contenu du panier
$cart = getCart();

// Récupération du solde Miams si connecté
$miams = 0;
$statut_miams = "";
if (isLoggedIn()) {
    $stmtMiams = $pdo->prepare("SELECT solde_miams FROM Utilisateurs WHERE id_user = ?");
    $stmtMiams->execute([$_SESSION['user_id']]);
    $userMiams = $stmtMiams->fetch();
    $miams = $userMiams['solde_miams'] ?? 0;
    if ($miams < 150) $statut_miams = "Débutant 🥉";
    elseif ($miams < 500) $statut_miams = "Argent 🥈";
    else $statut_miams = "Or 🥇";
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'panier';
$pageTitle = 'Mon Panier';

// Inclure le header
include_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Amélioration de l'interface du Panier */
    .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
    .cart-table th { background: var(--color-coal-black); color: var(--color-sauce-cream); padding: 15px; text-align: left; }
    .cart-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .cart-item-info { display: flex; align-items: center; }
    .cart-item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 15px; background: #eee; }
    .cart-item-info h3 { margin: 0 0 5px 0; font-size: 1.2rem; color: var(--color-coal-black); }
    .quantity-input { width: 60px; padding: 8px; border: 1px solid var(--color-stone-gray); border-radius: 4px; text-align: center; font-size: 1.1rem; }
    .btn-remove { color: var(--color-grill-red); font-weight: bold; text-decoration: none; padding: 5px 10px; border-radius: 4px; border: 1px solid var(--color-grill-red); transition: all 0.3s; }
    .btn-remove:hover { background: var(--color-grill-red); color: white; }
    
    .cart-summary { background: var(--color-sauce-cream); padding: 25px; border-radius: 8px; margin-top: 30px; border: 2px solid var(--color-fry-gold); display: flex; justify-content: space-between; align-items: center; }
    .cart-total { text-align: left; }
    .cart-total p { font-size: 1.2rem; margin: 0; }
    .cart-total strong { color: var(--color-grill-red); font-size: 2.2rem; display: block; margin-top: 5px; }
    
    .cart-actions { display: flex; align-items: center; gap: 15px; }
    .btn-checkout { background: var(--color-fry-gold); color: var(--color-coal-black); font-size: 1.2rem; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn-checkout:hover { transform: scale(1.05); background: #FFD54F; }
    .btn-update { background: var(--color-coal-black); color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
    .btn-clear { color: var(--color-stone-gray); text-decoration: underline; }
    
    .empty-cart { text-align: center; padding: 50px 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .empty-cart p { font-size: 1.5rem; color: var(--color-stone-gray); margin-bottom: 20px; }
    
    /* Mobile */
    @media (max-width: 768px) {
        .cart-summary { flex-direction: column; text-align: center; gap: 20px; }
        .cart-actions { flex-direction: column; width: 100%; }
        .btn-checkout, .btn-update { width: 100%; text-align: center; }
        .cart-table th:nth-child(2), .cart-table td:nth-child(2) { display: none; } /* Cacher prix unitaire sur mobile */
    }
</style>

<section class="cart-section">
    <div class="container">
        <h1>Mon Panier</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart['items'])): ?>
            <div class="empty-cart">
                <div style="font-size: 4rem; margin-bottom: 20px;">🛒</div>
                <p>Votre panier est tristement vide...</p>
                <a href="/api/pages/carte.php" class="btn-primary" style="padding: 15px 30px; font-size: 1.2rem;">Découvrir la carte</a>
            </div>
        <?php else: ?>
            <form action="/api/panier.php" method="post" class="cart-form">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="cart-items">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart['items'] as $index => $item): ?>
                                <tr>
                                    <td class="cart-item-info">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="cart-item-image">
                                        <?php else: ?>
                                            <div class="cart-item-image" style="display:flex; align-items:center; justify-content:center; font-size: 2rem;">🍔</div>
                                        <?php endif; ?>
                                        <div>
                                            <h3><?= htmlspecialchars($item['nom']) ?></h3>
                                            <?php if (!empty($item['options'])): ?>
                                                <p class="cart-item-options">
                                                    Options: <?= htmlspecialchars(implode(', ', $item['options'])) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['prix_unitaire'], 2, ',', ' ') ?> €</td>
                                    <td>
                                        <input type="number" name="quantite[<?= $index ?>]" value="<?= $item['quantite'] ?>" min="1" max="10" class="quantity-input">
                                    </td>
                                    <td><?= number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' ') ?> €</td>
                                    <td>
                                    <a href="/api/panier.php?action=remove&index=<?= $index ?>" class="btn-remove" title="Retirer">X</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isLoggedIn()): ?>
                <div class="loyalty-box" style="background: #fffdf7; padding: 20px; border-radius: 8px; margin-top: 20px; border: 2px dashed var(--color-fry-gold);">
                    <h3 style="color: var(--color-coal-black); margin-bottom: 10px;">🎁 Options Le Grand Miam Club</h3>
                    <p>Votre solde : <strong><?= $miams ?> Miams</strong> (Statut : <?= $statut_miams ?>)</p>
                    
                    <?php if ($miams >= 100): ?>
                        <div style="margin-top: 15px; padding: 10px; background: #e8f5e9; border-radius: 4px;">
                            <label style="font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="use_miams" value="100" style="width: 20px; height: 20px;">
                                Utiliser 100 Miams pour obtenir -10,00 € sur cette commande
                            </label>
                        </div>
                    <?php else: ?>
                        <p style="font-size: 0.95rem; color: #666; margin-top: 10px;"><em>Encore <strong><?= 100 - $miams ?> Miams</strong> pour débloquer votre première réduction de 10€ !</em></p>
                    <?php endif; ?>
                    
                    <p style="font-size: 0.9rem; margin-top: 10px; color: var(--color-grill-red);">
                        ✨ Cette commande va vous rapporter environ <strong><?= floor($cart['total'] * 10) ?> Miams</strong> !
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="cart-summary">
                    <div class="cart-total">
                        <p>Total de la commande</p>
                        <strong><?= number_format($cart['total'], 2, ',', ' ') ?> €</strong>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="/api/panier.php?action=clear" class="btn-clear">Vider le panier</a>
                        <button type="submit" class="btn-update" title="Recalculer">🔄 Maj</button>
                        <?php if (isLoggedIn()): ?>
                            <a href="/api/commander.php" class="btn-checkout">Payer la commande 💳</a>
                        <?php else: ?>
                            <a href="/api/pages/connexion.php?error=must_login" class="btn-checkout" style="background: var(--color-grill-red); color: white;">Me connecter pour payer 🔒</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/includes/footer.php';
?>