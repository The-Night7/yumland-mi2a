<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/panier.php';
require_once __DIR__ . '/includes/plats.php';

// Traiter les actions sur le panier
$message = '';

// Info depuis la modification de commande
if (isset($_GET['info']) && $_GET['info'] === 'editing' && isset($_SESSION['edit_commande_id'])) {
    $message = '✏️ Vous modifiez actuellement la commande #' . $_SESSION['edit_commande_id'] . '. Ajustez vos plats et cliquez sur Enregistrer !';
}

// Action: Mettre à jour la quantité OU soumission via bouton Enregistrer/Payer/Checkout
if (isset($_POST['action']) && ($_POST['action'] === 'update' || $_POST['action'] === 'save_edit' || $_POST['action'] === 'checkout')) {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Erreur de sécurité, veuillez réessayer.';
        // On bloque formellement la suite de l'exécution pour protéger la base de données
        $_POST['action'] = ''; 
    } else {
        foreach ($_POST['quantite'] as $index => $quantite) {
            $note = $_POST['note'][$index] ?? null;
            updateCartQuantity($index, (int)$quantite, $note);
        }
        
        if ($_POST['action'] === 'checkout') {
            // Sauvegarde de l'adresse en session avant d'aller vers CYBank
            $_SESSION['adresse_livraison_temp'] = trim($_POST['adresse_livraison'] ?? '');
            header('Location: /api/commander.php');
            exit;
        }
        
        if ($_POST['action'] === 'update') {
            $message = 'Le panier a été mis à jour.';
        }
    }
}

// Action: Sauvegarder l'édition d'une commande
if ((isset($_GET['action']) && $_GET['action'] === 'save_edit') || (isset($_POST['action']) && $_POST['action'] === 'save_edit')) {
    if (isset($_SESSION['edit_commande_id'])) {
        $id_commande = $_SESSION['edit_commande_id'];
        $cart = getCart(); // On rafraîchit le panier après l'update ci-dessus !
        $adresse_livraison = trim($_POST['adresse_livraison'] ?? '');
        
        if (!empty($cart['items'])) {
            // Application du statut LÉGENDE DU STEAK (-10%)
            $stmtMiams = $pdo->prepare("SELECT total_miams_historique FROM Utilisateurs WHERE id_user = ?");
            $stmtMiams->execute([$_SESSION['user_id']]);
            $miams_historique = $stmtMiams->fetchColumn() ?: 0;
            if ($miams_historique >= 3000) {
                $cart['total'] = $cart['total'] * 0.90;
            }

            // Calcul de l'ancien total pour vérifier s'il y a une différence à payer
            $stmt = $pdo->prepare("SELECT prix_total FROM Commandes WHERE id_commande = ? AND id_client = ?");
            $stmt->execute([$id_commande, $_SESSION['user_id']]);
            $old_total = $stmt->fetchColumn();
            
            if ($old_total !== false && $cart['total'] > $old_total) {
                if (!empty($adresse_livraison)) {
                    $pdo->prepare("UPDATE Commandes SET adresse_livraison = ? WHERE id_commande = ? AND id_client = ?")->execute([$adresse_livraison, $id_commande, $_SESSION['user_id']]);
                }
                // Différence à payer -> Redirection vers la passerelle de paiement
                header('Location: /api/commander.php?mode=supplement');
                exit;
            }
            
            // Si le prix est identique ou inférieur, on met à jour directement (sans paiement)
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE Commandes SET prix_total = ?, adresse_livraison = COALESCE(NULLIF(?, ''), adresse_livraison) WHERE id_commande = ? AND id_client = ?");
                $stmt->execute([$cart['total'], $adresse_livraison, $id_commande, $_SESSION['user_id']]);
                
                $pdo->prepare("DELETE FROM Contenu_Commandes WHERE id_commande = ?")->execute([$id_commande]);
                
                $stmtContenu = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES (?, ?, ?, ?, ?)");
                foreach ($cart['items'] as $item) {
                    $options = $item['options'] ?? [];
                    if (!empty($item['note'])) {
                        $options[] = "📝 " . $item['note'];
                    }
                    $optionsJson = json_encode($options);
                    $id_produit = $item['plat_id'] ?? $item['id'];
                    $stmtContenu->execute([$id_commande, $id_produit, $item['quantite'], $item['prix_unitaire'], $optionsJson]);
                }
                
                $pdo->commit();
                clearCart();
                unset($_SESSION['edit_commande_id']);
                
                header('Location: /api/client/commandes.php?success=commande_modifiee');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Erreur lors de la modification de la commande.';
            }
        }
    }
}

// Action: Annuler la modification
if (isset($_GET['action']) && $_GET['action'] === 'cancel_edit') {
    clearCart();
    unset($_SESSION['edit_commande_id']);
    header('Location: /api/client/commandes.php');
    exit;
}

// Action: Supprimer un élément du panier
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (removeFromCart($index)) {
        $message = 'L\'article a été retiré du panier.';
    }
}

// Action: Vider le panier
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    clearCart();
    $message = 'Votre panier a été vidé.';
}

// Récupérer le contenu du panier
$cart = getCart();

$subtotal = $cart['total'];
$discount = 0;

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
        $discount = $subtotal * 0.10;
        $cart['total'] = $subtotal - $discount;
    }
}

// Calcul de la différence si on est en train de modifier une commande
$difference = 0;
if (isset($_SESSION['edit_commande_id'])) {
    $stmtDiff = $pdo->prepare("SELECT prix_total FROM Commandes WHERE id_commande = ? AND id_client = ?");
    $stmtDiff->execute([$_SESSION['edit_commande_id'], $_SESSION['user_id']]);
    $old_total = $stmtDiff->fetchColumn();
    if ($old_total !== false) {
        $difference = $cart['total'] - $old_total;
    }
}

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
                <div class="empty-cart-icon">🛒</div>
                <p>Votre panier est tristement vide...</p>
                <?php if (isset($_SESSION['edit_commande_id'])): ?>
                    <a href="/api/panier.php?action=cancel_edit" class="btn-primary btn-cancel-edit">Annuler la modification</a>
                <?php endif; ?>
                <a href="/api/pages/carte.php" class="btn-primary btn-discover">Découvrir la carte</a>
            </div>
        <?php else: ?>
            <form action="/api/panier.php" method="post" class="cart-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="cart-items">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Total</th>
                                <th class="cart-actions-header">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart['items'] as $index => $item): ?>
                                <tr>
                                    <td class="cart-item-info">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="<?= str_starts_with($item['image'], '/') ? htmlspecialchars($item['image']) : '/' . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="cart-item-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="cart-item-image fallback-img" style="display:none;">🍔</div>
                                        <?php else: ?>
                                            <div class="cart-item-image fallback-img" style="display:flex;">🍔</div>
                                        <?php endif; ?>
                                        <div class="cart-item-details">
                                            <h3><?= htmlspecialchars($item['nom']) ?></h3>
                                            <?php 
                                            $options_dispos = $item['options_dispos'] ?? '[]';
                                            ?>
                                            <?php if (!empty($item['options']) || $options_dispos !== '[]'): ?>
                                                <p class="cart-item-options-text">
                                                    Options: <?= !empty($item['options']) ? htmlspecialchars(is_array($item['options']) ? implode(', ', $item['options']) : $item['options']) : 'Aucune' ?>
                                                    <?php if ($options_dispos !== '[]'): ?>
                                                        <br><button type="button" onclick="showOptionsModal(<?= $item['plat_id'] ?? $item['id'] ?>, '<?= htmlspecialchars($item['nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($options_dispos, ENT_QUOTES) ?>', 0, <?= $index ?>)" class="btn-edit-options"><i class="fas fa-edit"></i> Modifier les choix du menu</button>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <textarea name="note[<?= $index ?>]" placeholder="Modifications (ex: sans cornichons, changer Coca en Sprite...)" class="cart-note"><?= htmlspecialchars($item['note'] ?? '') ?></textarea>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['prix_unitaire'], 2, ',', ' ') ?> €</td>
                                    <td>
                                        <input type="number" name="quantite[<?= $index ?>]" value="<?= $item['quantite'] ?>" min="1" max="10" class="quantity-input" onchange="document.getElementById('btn-update-cart').click();">
                                    </td>
                                    <td><?= number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' ') ?> €</td>
                                    <td class="cart-action-cell">
                                        <a href="/api/panier.php?action=remove&index=<?= $index ?>" class="btn-remove btn-remove-wrapper" title="Retirer ce plat">
                                            <i class="fas fa-trash-alt"></i> Retirer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isLoggedIn()): ?>
                <?php
                $current_address = '';
                if (isset($_SESSION['edit_commande_id'])) {
                    $stmtAddr = $pdo->prepare("SELECT adresse_livraison FROM Commandes WHERE id_commande = ? AND id_client = ?");
                    $stmtAddr->execute([$_SESSION['edit_commande_id'], $_SESSION['user_id']]);
                    $current_address = $stmtAddr->fetchColumn() ?: '';
                }
                if (empty($current_address)) {
                    $stmtAddr = $pdo->prepare("SELECT adresse FROM Utilisateurs WHERE id_user = ?");
                    $stmtAddr->execute([$_SESSION['user_id']]);
                    $current_address = $stmtAddr->fetchColumn() ?: '';
                }
                ?>
                <div class="cart-address cart-address-box">
                    <h3 class="cart-address-title"><i class="fas fa-map-marker-alt" style="color: var(--color-primary);"></i> Adresse de livraison</h3>
                    <textarea name="adresse_livraison" rows="2" class="cart-address-input" placeholder="Où devons-nous vous livrer ?"><?= htmlspecialchars($current_address) ?></textarea>
                </div>
                
                <div class="loyalty-box" style="border: 3px solid <?= $color_miams ?>;">
                    <h3 class="loyalty-title">🥩 Le Grand Miam Club</h3>
                    <p>Miams disponibles : <strong><?= $miams_dispo ?> Miams</strong> (Rang : <strong style="color: <?= $color_miams ?>;"><?= $statut_miams ?></strong>)</p>
                    
                    <?php if ($statut_miams === "SAUCE CHEF" || $statut_miams === "LÉGENDE DU STEAK"): ?>
                        <div class="loyalty-benefit-tier1">
                            <p>🔥 Avantage Rang : Une portion de frites "Sweet Potato" offerte !</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($statut_miams === "LÉGENDE DU STEAK"): ?>
                        <div class="loyalty-benefit-tier2">
                            <p>👑 Avantage Ultime : -10% sur toute la carte & Livraison Prioritaire !</p>
                        </div>
                    <?php endif; ?>

                    <div class="loyalty-shop">
                        <p class="shop-title">Le Shop (Échangez vos Miams) :</p>
                        
                        <div class="shop-items">
                            <!-- Option 150 Miams -->
                            <div class="shop-item" style="background: <?= $miams_dispo >= 150 ? '#e8f5e9' : '#f5f5f5' ?>; opacity: <?= $miams_dispo >= 150 ? '1' : '0.5' ?>;">
                                <div><strong>150 Miams</strong> : Une Sauce Maison offerte 🥫</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick="showOptionsModal(<?= $id_sauce ?>, 'Sauce Maison', '[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Sauce BBQ&quot;,&quot;Sauce Béarnaise&quot;,&quot;Sauce au Poivre&quot;,&quot;Sauce Roquefort&quot;,&quot;Moutarde Ancienne&quot;]}]', 150)" 
                                    <?= $miams_dispo < 150 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                            
                            <!-- Option 300 Miams -->
                            <div class="shop-item" style="background: <?= $miams_dispo >= 300 ? '#e8f5e9' : '#f5f5f5' ?>; opacity: <?= $miams_dispo >= 300 ? '1' : '0.5' ?>;">
                                <div><strong>300 Miams</strong> : Un Soft ou une Bière (25cl) 🍺</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick="showOptionsModal(<?= $id_boisson ?>, 'Boisson Offerte', '[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Coca-Cola (33cl)&quot;,&quot;Sprite (33cl)&quot;,&quot;Ice Tea (25cl)&quot;,&quot;Bière Blonde (25cl)&quot;,&quot;Bière IPA (25cl)&quot;]}]', 300)" 
                                    <?= $miams_dispo < 300 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>

                            <!-- Option 800 Miams -->
                            <div class="shop-item" style="background: <?= $miams_dispo >= 800 ? '#e8f5e9' : '#f5f5f5' ?>; opacity: <?= $miams_dispo >= 800 ? '1' : '0.5' ?>;">
                                <div><strong>800 Miams</strong> : Un Dessert au choix 🍪</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick="showOptionsModal(<?= $id_dessert ?>, 'Dessert Offert', '[{&quot;titre&quot;:&quot;Choix&quot;,&quot;choix&quot;:[&quot;Cookie Skillet&quot;,&quot;Cheesecake NY&quot;,&quot;Brioche Perdue&quot;]}]', 800)" 
                                    <?= $miams_dispo < 800 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                            
                            <!-- Option 1500 Miams -->
                            <div class="shop-item" style="background: <?= $miams_dispo >= 1500 ? '#e8f5e9' : '#f5f5f5' ?>; opacity: <?= $miams_dispo >= 1500 ? '1' : '0.5' ?>;">
                                <div><strong>1500 Miams</strong> : Le Burger "Grand Miam" 🍔</div>
                                <button type="button" class="btn-primary shop-item-btn" 
                                    onclick="showOptionsModal(<?= $id_burger ?>, 'Burger Grand Miam', '[{&quot;titre&quot;:&quot;Viande&quot;,&quot;choix&quot;:[&quot;Bœuf Limousin&quot;,&quot;Bœuf (Halal)&quot;,&quot;Poulet Croustillant&quot;,&quot;Galette Veggie&quot;]},{&quot;titre&quot;:&quot;Cuisson&quot;,&quot;choix&quot;:[&quot;Saignant&quot;,&quot;À point&quot;,&quot;Bien cuit&quot;]}]', 1500)" 
                                    <?= $miams_dispo < 1500 ? 'disabled' : '' ?>>Obtenir</button>
                            </div>
                        </div>
                    </div>
                    
                    <p class="loyalty-earn-info">
                        ✨ En réglant cette commande, vous cumulerez <strong><?= floor($cart['total'] * 10) ?> Miams</strong> supplémentaires !
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="cart-summary">
                    <div class="cart-total">
                        <p>Total de la commande</p>
                        <?php if ($discount > 0): ?>
                            <div class="discount-old-price"><?= number_format($subtotal, 2, ',', ' ') ?> €</div>
                            <strong class="discount-new-price"><?= number_format($cart['total'], 2, ',', ' ') ?> €</strong>
                            <p class="discount-info">✨ Remise LÉGENDE DU STEAK (-10%) appliquée !</p>
                        <?php else: ?>
                            <strong><?= number_format($cart['total'], 2, ',', ' ') ?> €</strong>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-actions">
                        <button type="submit" name="action" value="update" id="btn-update-cart" class="btn-update btn-update-cart-action">🔄 Actualiser</button>
                        <?php if (isset($_SESSION['edit_commande_id'])): ?>
                            <a href="/api/panier.php?action=cancel_edit" class="btn-clear">Annuler la modification</a>
                            <?php if ($difference > 0): ?>
                                <button type="submit" name="action" value="save_edit" class="btn-checkout btn-pay-supplement">💳 Payer supplément (<?= number_format($difference, 2, ',', ' ') ?> €)</button>
                            <?php else: ?>
                                <button type="submit" name="action" value="save_edit" class="btn-checkout btn-save-edit">💾 Enregistrer</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/api/panier.php?action=clear" class="btn-clear">Vider le panier</a>
                            <?php if (isLoggedIn()): ?>
                                <button type="submit" name="action" value="checkout" class="btn-checkout btn-pay-order">Payer la commande 💳</button>
                            <?php else: ?>
                                <a href="/api/pages/connexion.php?error=must_login" class="btn-checkout btn-login-pay">Me connecter pour payer 🔒</a>
                            <?php endif; ?>
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