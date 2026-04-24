<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/panier.php';

// Inclusion de getapikey.php si CYBank exige un hachage 'control' à l'envoi
$apiKeyFile = __DIR__ . '/includes/getapikey.php';
if (file_exists($apiKeyFile)) {
    require_once $apiKeyFile;
}

// 1. Sécurité : l'utilisateur doit être connecté pour payer
if (!isLoggedIn()) {
    redirect('/api/pages/connexion.php?error=must_login');
}

$cart = getCart();

// 2. Redirection si le panier est vide
if (empty($cart['items'])) {
    redirect('/api/panier.php');
}

$id_client = $_SESSION['user_id'];

// Application du statut LÉGENDE DU STEAK (-10%)
$stmtMiams = $pdo->prepare("SELECT total_miams_historique FROM Utilisateurs WHERE id_user = ?");
$stmtMiams->execute([$id_client]);
$miams_historique = $stmtMiams->fetchColumn() ?: 0;
if ($miams_historique >= 3000) {
    $cart['total'] = $cart['total'] * 0.90;
}

$total_paye = $cart['total'];

// ==============================================================
// ETAPE 1 : Sauvegarde de la commande "En attente" dans la BDD
// ==============================================================
try {
    $pdo->beginTransaction();

    // Mode Supplément (Modification d'une commande existante avec augmentation de prix)
    $is_supplement = isset($_SESSION['edit_commande_id']) && isset($_GET['mode']) && $_GET['mode'] === 'supplement';

    if ($is_supplement) {
        $id_commande = $_SESSION['edit_commande_id'];
        
        // Calcul du supplément exact
        $stmt = $pdo->prepare("SELECT prix_total FROM Commandes WHERE id_commande = ?");
        $stmt->execute([$id_commande]);
        $old_total = $stmt->fetchColumn();
        
        $difference = $cart['total'] - $old_total;
        if ($difference <= 0) redirect('/api/panier.php');
        
        $total_paye = $difference; // On ne demande que la différence à la banque !
        
        // Mise à jour de la commande avec le nouveau prix global
        $pdo->prepare("UPDATE Commandes SET prix_total = ? WHERE id_commande = ?")->execute([$cart['total'], $id_commande]);
        
        // Remplacement des anciens plats par les nouveaux
        $pdo->prepare("DELETE FROM Contenu_Commandes WHERE id_commande = ?")->execute([$id_commande]);
        $stmtContenu = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES (?, ?, ?, ?, ?)");
        foreach ($cart['items'] as $item) {
            $options = $item['options'] ?? [];
            if (!empty($item['note'])) $options[] = "📝 " . $item['note'];
            $optionsJson = json_encode($options);
            $id_produit = $item['plat_id'] ?? $item['id'];
            $stmtContenu->execute([$id_commande, $id_produit, $item['quantite'], $item['prix_unitaire'], $optionsJson]);
        }
        
        // Fin de la session d'édition
        unset($_SESSION['edit_commande_id']);
    } else {
        // Création standard d'une nouvelle commande
        $adresse_livraison = $_SESSION['adresse_livraison_temp'] ?? '';
        unset($_SESSION['adresse_livraison_temp']);
        
        if (empty($adresse_livraison)) {
            $stmtUser = $pdo->prepare("SELECT adresse FROM Utilisateurs WHERE id_user = ?");
            $stmtUser->execute([$id_client]);
            $user = $stmtUser->fetch();
            $adresse_livraison = $user['adresse'] ?? '';
        }

        $stmt = $pdo->prepare("INSERT INTO Commandes (id_client, prix_total, statut, paiement_statut, date_commande, adresse_livraison) VALUES (?, ?, 'En attente', 'Non payé', NOW(), ?)");
        $stmt->execute([$id_client, $total_paye, $adresse_livraison]);
        $id_commande = $pdo->lastInsertId();

        $stmtContenu = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES (?, ?, ?, ?, ?)");
        foreach ($cart['items'] as $item) {
            $optionsJson = json_encode($item['options'] ?? []);
            $id_produit = $item['plat_id'] ?? $item['id'];
            $stmtContenu->execute([$id_commande, $id_produit, $item['quantite'], $item['prix_unitaire'], $optionsJson]);
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de la création de la commande : " . $e->getMessage());
}

// ==============================================================
// ETAPE 2 : Préparation des données pour CY BANK
// ==============================================================
$vendeur = "MI-2_A"; // Code vendeur valide selon l'annexe (Groupe MI2A)

// La banque exige une transaction de 10 caractères mini. Le sprintf rajoute des 0 pour combler (ex: MI2A000014)
$transaction = sprintf("MI2A%06d", $id_commande); 

// Attention, la banque veut un point pour les décimales, pas de virgule. Le number_format sert à forcer ça.
$montant = number_format($total_paye, 2, '.', '');

// URL de retour une fois le paiement terminé
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$url_retour = $protocol . $_SERVER['HTTP_HOST'] . "/api/retour_paiement.php";

// Génération du contrôle si CYBank le demande lors de la requête
$control = "";
if (function_exists('getAPIKey')) {
    $api_key = getAPIKey($vendeur);
    // Application stricte de la règle de hachage CY Bank
    $control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $url_retour . "#");
}

// URL officielle de l'interface Web de paiement CY Bank
$cybank_url = "https://www.plateforme-smc.fr/cybank/index.php"; 

$pageTitle = 'Redirection CY BANK';
include_once __DIR__ . '/includes/header.php';
?>

<style>
    .redirect-wrapper {
        background-color: var(--color-bg, #FDFBF7); /* Crème Sauce */
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .redirect-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(45, 45, 45, 0.15); /* Ombre charbon */
        border-top: 6px solid var(--color-primary, #D32F2F); /* Rouge Grill */
        padding: 50px 30px;
        text-align: center;
        max-width: 500px;
        width: 100%;
    }
    .redirect-container h2 {
        color: var(--color-secondary, #2D2D2D); /* Noir Charbon */
        margin-bottom: 10px;
        font-family: 'Oswald', sans-serif;
        text-transform: uppercase;
    }
    .spinner {
        font-size: 4rem;
        color: var(--color-primary, #D32F2F);
        margin-bottom: 20px;
        animation: spin 1.2s linear infinite;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
    .btn-force {
        display: inline-block;
        margin-top: 25px;
        padding: 12px 25px;
        background: var(--color-primary, #D32F2F);
        color: var(--color-bg, #FDFBF7);
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        transition: transform 0.2s, background 0.2s;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .btn-force:hover { background: #B71C1C; transform: scale(1.02); }
</style>

<div class="redirect-wrapper">
    <div class="redirect-container">
        <i class="fas fa-circle-notch spinner"></i>
        <h2>Connexion à CY BANK...</h2>
        <p style="color: #2D2D2D;">Veuillez patienter, nous vous transférons vers le portail de paiement sécurisé de l'école.</p>
        <p style="font-size: 1.2rem; margin-top: 15px; color: #2D2D2D;">Montant : <strong style="color: #D32F2F; font-size: 1.4rem;"><?= number_format($total_paye, 2, ',', ' ') ?> €</strong></p>

        <!-- Formulaire invisible de redirection vers CYBank -->
        <form id="cybank-form" action="<?= htmlspecialchars($cybank_url) ?>" method="POST">
            <input type="hidden" name="vendeur" value="<?= htmlspecialchars($vendeur) ?>">
            <input type="hidden" name="montant" value="<?= htmlspecialchars($montant) ?>">
            <input type="hidden" name="transaction" value="<?= htmlspecialchars($transaction) ?>">
            <input type="hidden" name="retour" value="<?= htmlspecialchars($url_retour) ?>">
            <?php if (!empty($control)): ?>
            <input type="hidden" name="control" value="<?= htmlspecialchars($control) ?>">
            <?php endif; ?>
        </form>

        <a href="#" onclick="document.getElementById('cybank-form').submit(); return false;" class="btn-force">
            Forcer la redirection
        </a>
    </div>
</div>

<script>
    // Déclenche l'envoi du formulaire vers CY Bank après 1.5 seconde
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.getElementById('cybank-form').submit();
        }, 1500);
    });
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>