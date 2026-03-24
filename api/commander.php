<?php
/**
 * api/commander.php
 * Calcule le total réel via SQL et redirige vers CYBank.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/panier.php';

if (!isset($_SESSION['cart']['items']) || empty($_SESSION['cart']['items'])) {
    header('Location: /api/pages/carte.php?error=panier_vide');
    exit;
}

// Recalculer le total par sécurité
updateCartTotal();
$total = $_SESSION['cart']['total'];

// Paramètres CYBank
$vendeur = "MI-2_A";
$session = session_id();
$url_retour = "http://localhost:8000/api/retour_paiement.php";

$query = http_build_query([
    'vendeur' => $vendeur,
    'montant' => $total,
    'session' => $session,
    'url_retour' => $url_retour
]);

// Redirection vers l'interface officielle
header("Location: https://www.plateforme-smc.fr/cybank/?" . $query);
exit;

?>
