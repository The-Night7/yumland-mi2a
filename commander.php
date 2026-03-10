<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/panier.php';
require_once __DIR__ . '/includes/commandes.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    // Sauvegarder l'URL actuelle pour rediriger après connexion
    $_SESSION['redirect_after_login'] = '/commander.php';
    redirect('/public/html/connexion.php');
}

// Vérifier si le panier n'est pas vide
$cart = getCart();
if (empty($cart['items'])) {
    redirect('/panier.php');
}

// Récupérer les informations de l'utilisateur
$user = getUserById($_SESSION['user_id']);

$error = '';
$success = '';

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Erreur de sécurité, veuillez réessayer.';
    } else {
        $mode = $_POST['mode'] ?? '';
        $adresse_livraison = $_POST['adresse_livraison'] ?? '';
        $date_programmee = $_POST['date_programmee'] ?? '';
        
        // Validation des champs
        if (empty($mode)) {
            $error = 'Veuillez sélectionner un mode de commande.';
        } elseif ($mode === 'livraison' && empty($adresse_livraison)) {
            $error = 'Veuillez saisir une adresse de livraison.';
        } else {
            // Préparer les données de la commande
            $commandeData = [
                'user_id' => $_SESSION['user_id'],
                'date' => $date_programmee ? $date_programmee : date('Y-m-d\TH:i:s'),
                'status' => 'en préparation',
                'mode' => $mode,
                'adresse_livraison' => $mode === 'livraison' ? $adresse_livraison : null,
                'montant_total' => $cart['total'],
                'livreur_id' => null,
                'details' => []
            ];
            
            // Ajouter les détails de la commande
            foreach ($cart['items'] as $item) {
                $commandeData['details'][] = [
                    'plat_id' => $item['plat_id'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'options' => $item['options']
                ];
            }
            
            // Enregistrer la commande
            $commande_id = createCommande($commandeData);
            
            if ($commande_id) {
                // Vider le panier
                clearCart();
                
                // Rediriger vers la page de paiement
                redirect('/paiement.php?commande_id=' . $commande_id);
            } else {
                $error = 'Une erreur est survenue lors de la création de la commande.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'commander';
$pageTitle = 'Commander';

// Inclure le header
include_once __DIR__ . '/includes/header.php';
?>

<section class="order-section">
    <div class="container">
        <h1>Finaliser ma commande</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="order-container">
            <div class="order-summary">
                <h2>Récapitulatif de la commande</h2>
                
                <div class="order-items">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="order-item">
                            <div class="order-item-info">
                                <h3><?= htmlspecialchars($item['nom']) ?></h3>
                                <p>Quantité: <?= $item['quantite'] ?></p>
                                <?php if (!empty($item['options'])): ?>
                                    <p class="order-item-options">
                                        Options: <?= htmlspecialchars(implode(', ', $item['options'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="order-item-price">
                                <?= number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' ') ?> €
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-total">
                    <p>Total: <strong><?= number_format($cart['total'], 2, ',', ' ') ?> €</strong></p>
                </div>
            </div>
            
            <div class="order-form">
                <h2>Informations de livraison</h2>
                
                <form action="/commander.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-group">
                        <label>Mode de commande</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="mode" value="sur place" required> Sur place
                            </label>
                            <label>
                                <input type="radio" name="mode" value="à emporter"> À emporter
                            </label>
                            <label>
                                <input type="radio" name="mode" value="livraison"> Livraison
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="adresse-livraison-group" style="display: none;">
                        <label for="adresse_livraison">Adresse de livraison</label>
                        <textarea id="adresse_livraison" name="adresse_livraison" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Quand souhaitez-vous être servi ?</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="timing" value="now" checked> Dès que possible
                            </label>
                            <label>
                                <input type="radio" name="timing" value="later"> Programmer
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="date-programmee-group" style="display: none;">
                        <label for="date_programmee">Date et heure</label>
                        <input type="datetime-local" id="date_programmee" name="date_programmee" min="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    
                    <button type="submit" class="btn-primary">Procéder au paiement</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    // Afficher/masquer l'adresse de livraison en fonction du mode de commande
    document.querySelectorAll('input[name="mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const adresseGroup = document.getElementById('adresse-livraison-group');
            if (this.value === 'livraison') {
                adresseGroup.style.display = 'block';
            } else {
                adresseGroup.style.display = 'none';
            }
        });
    });
    
    // Afficher/masquer la date programmée
    document.querySelectorAll('input[name="timing"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const dateGroup = document.getElementById('date-programmee-group');
            if (this.value === 'later') {
                dateGroup.style.display = 'block';
            } else {
                dateGroup.style.display = 'none';
            }
        });
    });
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/includes/footer.php';
?>