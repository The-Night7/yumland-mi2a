<?php
/**
 * api/commander.php
 * Calcule le total réel via SQL et redirige vers CYBank.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/panier.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/getapikey.php';

// Bloquer l'accès si l'utilisateur n'est pas connecté
if (!isLoggedIn()) {
    header('Location: /api/pages/connexion.php?error=must_login');
    exit;
}

if (!isset($_SESSION['cart']['items']) || empty($_SESSION['cart']['items'])) {
    header('Location: /api/pages/carte.php?error=panier_vide');
    exit;
}

// Recalculer le total par sécurité
updateCartTotal();
$total = $_SESSION['cart']['total'];
$user_id = $_SESSION['user_id'];

// 1. CRÉATION DE LA COMMANDE EN BASE D'ABORD (Statut 'En attente')
try {
    // On récupère l'adresse de l'utilisateur pour l'attacher à la commande
    $stmtUser = $pdo->prepare("SELECT adresse FROM Utilisateurs WHERE id_user = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch();
    $adresse_livraison = $user['adresse'] ?? 'Adresse non renseignée';

    // On insère la commande pour générer un VRAI id_trans (numéro de commande SQL)
    $stmt = $pdo->prepare("INSERT INTO Commandes (id_client, prix_total, statut, paiement_statut, adresse_livraison) VALUES (?, ?, 'En attente', 'En cours de paiement', ?)");
    $stmt->execute([$user_id, $total, $adresse_livraison]);
    $id_commande = $pdo->lastInsertId();

    // On insère le contenu du panier
    $stmtItem = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES (?, ?, ?, ?, ?)");
    foreach ($_SESSION['cart']['items'] as $item) {
        $id_prod = $item['plat_id'] ?? $item['id'] ?? 1;
        $opts_str = '';
        if (!empty($item['options'])) {
            $opts_str = is_array($item['options']) ? implode(', ', $item['options']) : $item['options'];
        }
        
        $stmtItem->execute([$id_commande, $id_prod, $item['quantite'], $item['prix_unitaire'], $opts_str]);
    }
} catch (Exception $e) {
    die("Erreur de création de commande : " . $e->getMessage());
}

// Paramètres CYBank (Respect strict de la documentation)
$vendeur = "MI-2_A"; 
$url_retour = "http://localhost:8000/api/retour_paiement.php";

// Formatage du montant (décimal exact avec .)
$montant = number_format($total, 2, '.', '');

// Identifiant transaction: Doit faire entre 10 et 24 caractères alphanumériques
// On préfixe par MI2A et on complète avec des zéros (ex: MI2A00000028)
$transaction = "MI2A" . str_pad($id_commande, 8, "0", STR_PAD_LEFT);

// Calcul de la signature de sécurité (Control)
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $url_retour . "#");
?>
<!DOCTYPE html>
<html lang="fr">
<head><title>Redirection CYBank...</title></head>
<body style="text-align:center; padding-top: 100px; font-family: sans-serif; background-color: #FDFBF7;" onload="document.getElementById('cybank-form').submit();">
    <h2>Connexion à la plateforme sécurisée CYBank...</h2>
    <p>Veuillez patienter, vous allez être redirigé vers l'interface de paiement.</p>
    
    <form id="cybank-form" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?= htmlspecialchars($transaction) ?>">
        <input type="hidden" name="montant" value="<?= htmlspecialchars($montant) ?>">
        <input type="hidden" name="vendeur" value="<?= htmlspecialchars($vendeur) ?>">
        <input type="hidden" name="retour" value="<?= htmlspecialchars($url_retour) ?>">
        <input type="hidden" name="control" value="<?= htmlspecialchars($control) ?>">
    </form>
</body>
</html>
