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

// Calcul des Miams déjà utilisés dans le panier actuel
$miams_used = 0;
foreach ($cart['items'] as $item) {
    if (!empty($item['options']) && is_array($item['options'])) {
        foreach ($item['options'] as $opt) {
            if (preg_match('/-\s*([0-9]+)\s*Miams/', $opt, $matches)) {
                $miams_used += (int)$matches[1];
            }
        }
    }
}

// Recherche des IDs génériques pour associer les récompenses Miams
$stmtProd = $pdo->query("SELECT id_produit, nom FROM Produits");
$produits_db = $stmtProd->fetchAll();
$id_sauce = 1; $id_boisson = 1; $id_dessert = 1; $id_burger = 1;
foreach($produits_db as $p) { if(stripos($p['nom'], 'Sauce') !== false) $id_sauce = $p['id_produit']; }
foreach($produits_db as $p) { if(stripos($p['nom'], 'Sodas') !== false || stripos($p['nom'], 'Boisson') !== false) $id_boisson = $p['id_produit']; }
foreach($produits_db as $p) { if(stripos($p['nom'], 'Cookie') !== false || stripos($p['nom'], 'Dessert') !== false) $id_dessert = $p['id_produit']; }
foreach($produits_db as $p) { if(stripos($p['nom'], 'Grand Miam') !== false) $id_burger = $p['id_produit']; }

// Récupération du solde Miams si connecté
$miams = 0;
$statut_miams = "";
$color_miams = "var(--color-stone-gray)"; // Couleur par défaut (Niveau 1)

if (isLoggedIn()) {
    $stmtMiams = $pdo->prepare("SELECT solde_miams, total_miams_historique FROM Utilisateurs WHERE id_user = ?");
    $stmtMiams->execute([$_SESSION['user_id']]);
    $userMiams = $stmtMiams->fetch();
    $miams = $userMiams['solde_miams'] ?? 0;
    $miams_historique = $userMiams['total_miams_historique'] ?? $miams;
    
    // Application des Paliers de Fidélité (D'après la doc)
    if ($miams_historique < 1000) {
        $statut_miams = "PETIT GRILLEUR";
        $color_miams = "var(--color-grey-light)"; // #BDBDBD
    } elseif ($miams_historique < 3000) {
        $statut_miams = "SAUCE CHEF";
        $color_miams = "var(--color-primary)"; // #D32F2F
    } else {
        $statut_miams = "LÉGENDE DU STEAK";
        $color_miams = "var(--color-accent)"; // #FFC107
    }
}

// Calcul du solde prévisionnel en soustrayant ceux du panier
$miams_dispo = max(0, $miams - $miams_used);

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
    .cart-table th { background: var(--color-secondary); color: var(--color-bg); padding: 15px; text-align: left; }
    .cart-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .cart-item-info { display: flex; align-items: center; }
    .cart-item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 15px; background: #eee; }
    .cart-item-info h3 { margin: 0 0 5px 0; font-size: 1.2rem; color: var(--color-secondary); }
    .quantity-input { width: 60px; padding: 8px; border: 1px solid var(--color-grey-light); border-radius: 4px; text-align: center; font-size: 1.1rem; }
    .btn-remove { color: var(--color-primary); font-weight: bold; text-decoration: none; padding: 5px 10px; border-radius: 4px; border: 1px solid var(--color-primary); transition: all 0.3s; }
    .btn-remove:hover { background: var(--color-primary); color: white; }
    
    .cart-summary { background: var(--color-bg); padding: 25px; border-radius: 8px; margin-top: 30px; border: 2px solid var(--color-accent); display: flex; justify-content: space-between; align-items: center; }
    .cart-total { text-align: left; }
    .cart-total p { font-size: 1.2rem; margin: 0; }
    .cart-actions { display: flex; align-items: center; gap: 15px; }
    .cart-total strong { color: var(--color-primary); font-size: 2.2rem; display: block; margin-top: 5px; }
    
    .btn-checkout { background: var(--color-accent); color: var(--color-secondary); font-size: 1.2rem; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn-checkout:hover { transform: scale(1.05); background: #FFD54F; }
    .btn-update { background: var(--color-secondary); color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
    .btn-clear { color: var(--color-grey-light); text-decoration: underline; }
    
    .empty-cart { text-align: center; padding: 50px 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .empty-cart p { font-size: 1.5rem; color: var(--color-grey-light); margin-bottom: 20px; }
    
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
                                            <img src="<?= str_starts_with($item['image'], '/') ? htmlspecialchars($item['image']) : '/' . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="cart-item-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="cart-item-image fallback-img" style="display:none; align-items:center; justify-content:center; font-size: 2rem; background: #eee;">🍔</div>
                                        <?php else: ?>
                                            <div class="cart-item-image fallback-img" style="display:flex; align-items:center; justify-content:center; font-size: 2rem; background: #eee;">🍔</div>
                                        <?php endif; ?>
                                        <div>
                                            <h3><?= htmlspecialchars($item['nom']) ?></h3>
                                            <?php if (!empty($item['options'])): ?>
                                                <p class="cart-item-options">
                                                    Options: <?= htmlspecialchars(is_array($item['options']) ? implode(', ', $item['options']) : $item['options']) ?>
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
                <div class="loyalty-box" style="background: #fffdf7; padding: 20px; border-radius: 8px; margin-top: 20px; border: 3px solid <?= $color_miams ?>;">
                    <h3 style="color: var(--color-secondary); margin-bottom: 10px;">🥩 Le Grand Miam Club</h3>
                    <p>Miams disponibles : <strong><?= $miams_dispo ?> Miams</strong> (Rang : <strong style="color: <?= $color_miams ?>;"><?= $statut_miams ?></strong>)</p>
                    
                    <?php if ($statut_miams === "SAUCE CHEF" || $statut_miams === "LÉGENDE DU STEAK"): ?>
                        <div style="margin: 10px 0; padding: 10px; background: rgba(211, 47, 47, 0.1); border-left: 4px solid var(--color-primary); border-radius: 4px;">
                            <p style="color: var(--color-primary); font-weight: bold; margin: 0;">🔥 Avantage Rang : Une portion de frites "Sweet Potato" offerte !</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($statut_miams === "LÉGENDE DU STEAK"): ?>
                        <div style="margin: 10px 0; padding: 10px; background: var(--color-secondary); border-left: 4px solid var(--color-accent); border-radius: 4px;">
                            <p style="color: var(--color-accent); font-weight: bold; margin: 0;">👑 Avantage Ultime : -10% sur toute la carte & Livraison Prioritaire !</p>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 15px;">
                        <p style="font-weight: bold; margin-bottom: 10px;">Le Shop (Échangez vos Miams) :</p>
                        
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <!-- Option 150 Miams -->
                            <div style="padding: 10px; background: <?= $miams_dispo >= 150 ? '#e8f5e9' : '#f5f5f5' ?>; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; opacity: <?= $miams_dispo >= 150 ? '1' : '0.5' ?>;">
                                <div><strong>150 Miams</strong> : Une Sauce Maison offerte 🥫</div>
                                <button type="button" class="btn-primary" style="padding: 5px 15px; font-size: 0.9rem;" 
                                    onclick="showOptionsModal(<?= $id_sauce ?>, 'Sauce Maison', '[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Sauce BBQ&quot;,&quot;Sauce Béarnaise&quot;,&quot;Sauce au Poivre&quot;,&quot;Sauce Roquefort&quot;,&quot;Moutarde Ancienne&quot;]}]', 150)" 
                                    <?= $miams_dispo < 150 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                            
                            <!-- Option 300 Miams -->
                            <div style="padding: 10px; background: <?= $miams_dispo >= 300 ? '#e8f5e9' : '#f5f5f5' ?>; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; opacity: <?= $miams_dispo >= 300 ? '1' : '0.5' ?>;">
                                <div><strong>300 Miams</strong> : Un Soft ou une Bière (25cl) 🍺</div>
                                <button type="button" class="btn-primary" style="padding: 5px 15px; font-size: 0.9rem;" 
                                    onclick="showOptionsModal(<?= $id_boisson ?>, 'Boisson Offerte', '[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Coca-Cola (33cl)&quot;,&quot;Sprite (33cl)&quot;,&quot;Ice Tea (25cl)&quot;,&quot;Bière Blonde (25cl)&quot;,&quot;Bière IPA (25cl)&quot;]}]', 300)" 
                                    <?= $miams_dispo < 300 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>

                            <!-- Option 800 Miams -->
                            <div style="padding: 10px; background: <?= $miams_dispo >= 800 ? '#e8f5e9' : '#f5f5f5' ?>; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; opacity: <?= $miams_dispo >= 800 ? '1' : '0.5' ?>;">
                                <div><strong>800 Miams</strong> : Un Dessert au choix 🍪</div>
                                <button type="button" class="btn-primary" style="padding: 5px 15px; font-size: 0.9rem;" 
                                    onclick="showOptionsModal(<?= $id_dessert ?>, 'Dessert Offert', '[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Cookie Skillet&quot;,&quot;Cheesecake NY&quot;,&quot;Brioche Perdue&quot;]}]', 800)" 
                                    <?= $miams_dispo < 800 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                            
                            <!-- Option 1500 Miams -->
                            <div style="padding: 10px; background: <?= $miams_dispo >= 1500 ? '#e8f5e9' : '#f5f5f5' ?>; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; opacity: <?= $miams_dispo >= 1500 ? '1' : '0.5' ?>;">
                                <div><strong>1500 Miams</strong> : Le Burger "Grand Miam" 🍔</div>
                                <button type="button" class="btn-primary" style="padding: 5px 15px; font-size: 0.9rem;" 
                                    onclick="showOptionsModal(<?= $id_burger ?>, 'Burger Grand Miam', '[{&quot;titre&quot;:&quot;Viande&quot;,&quot;choix&quot;:[&quot;Bœuf Limousin&quot;,&quot;Bœuf (Halal)&quot;,&quot;Poulet Croustillant&quot;,&quot;Galette Veggie&quot;]},{&quot;titre&quot;:&quot;Cuisson&quot;,&quot;choix&quot;:[&quot;Saignant&quot;,&quot;À point&quot;,&quot;Bien cuit&quot;]}]', 1500)" 
                                    <?= $miams_dispo < 1500 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                        </div>
                    </div>
                    
                    <p style="font-size: 0.95rem; margin-top: 15px; color: var(--color-primary);">
                        ✨ En réglant cette commande, vous cumulerez <strong><?= floor($cart['total'] * 10) ?> Miams</strong> supplémentaires !
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
                            <a href="/api/pages/connexion.php?error=must_login" class="btn-checkout" style="background: var(--color-primary); color: white;">Me connecter pour payer 🔒</a>
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