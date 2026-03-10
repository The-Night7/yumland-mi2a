<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/commandes.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('/public/html/connexion.php');
}

// Vérifier si l'ID de commande est fourni
if (!isset($_GET['commande_id'])) {
    redirect('/index.php');
}

$commande_id = (int)$_GET['commande_id'];
$commande = getCommandeById($commande_id);

// Vérifier si la commande existe et appartient à l'utilisateur
if (!$commande || $commande['user_id'] != $_SESSION['user_id']) {
    redirect('/index.php');
}

$error = '';
$success = '';

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Erreur de sécurité, veuillez réessayer.';
    } else {
        $numero_carte = $_POST['numero_carte'] ?? '';
        $nom_carte = $_POST['nom_carte'] ?? '';
        $date_expiration = $_POST['date_expiration'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        
        // Validation des champs
        if (empty($numero_carte) || empty($nom_carte) || empty($date_expiration) || empty($cvv)) {
            $error = 'Veuillez remplir tous les champs.';
        } elseif (!preg_match('/^[0-9]{16}$/', str_replace(' ', '', $numero_carte))) {
            $error = 'Le numéro de carte doit contenir 16 chiffres.';
        } elseif (!preg_match('/^[0-9]{3}$/', $cvv)) {
            $error = 'Le code de sécurité doit contenir 3 chiffres.';
        } else {
            // Simulation d'appel à l'API CYBank
            $payment_success = simulateCYBankPayment($commande['montant_total']);
            
            if ($payment_success) {
                // Mettre à jour le statut de la commande si nécessaire
                // Dans ce cas, le statut reste "en préparation" car il a déjà été défini
                
                $success = 'Paiement effectué avec succès ! Votre commande est en cours de préparation.';
            } else {
                $error = 'Le paiement a échoué. Veuillez vérifier vos informations et réessayer.';
            }
        }
    }
}

// Fonction pour simuler un appel à l'API CYBank
function simulateCYBankPayment($amount) {
    // En production, vous appelleriez l'API CYBank ici
    // Pour la simulation, nous retournons toujours true (succès)
    return true;
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'paiement';
$pageTitle = 'Paiement';

// Inclure le header
include_once __DIR__ . '/includes/header.php';
?>

<section class="payment-section">
    <div class="container">
        <h1>Paiement</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
                <p>Vous allez être redirigé vers votre espace client dans quelques secondes...</p>
                <script>
                    setTimeout(function() {
                        window.location.href = '/client/commandes.php';
                    }, 5000);
                </script>
            </div>
        <?php else: ?>
            <div class="payment-container">
                <div class="payment-summary">
                    <h2>Récapitulatif</h2>
                    <p>Commande #<?= $commande_id ?></p>
                    <p>Total à payer: <strong><?= number_format($commande['montant_total'], 2, ',', ' ') ?> €</strong></p>
                </div>
                
                <div class="payment-form">
                    <h2>Informations de paiement</h2>
                    <p class="payment-info">Paiement sécurisé via CYBank</p>
                    
                    <form action="/paiement.php?commande_id=<?= $commande_id ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="form-group">
                            <label for="numero_carte">Numéro de carte</label>
                            <input type="text" id="numero_carte" name="numero_carte" placeholder="1234 5678 9012 3456" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nom_carte">Nom sur la carte</label>
                            <input type="text" id="nom_carte" name="nom_carte" placeholder="JEAN DUPONT" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_expiration">Date d'expiration</label>
                                <input type="text" id="date_expiration" name="date_expiration" placeholder="MM/AA" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv">Code de sécurité</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-primary">Payer maintenant</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/includes/footer.php';
?>